<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SupportConversationResource;
use App\Http\Resources\Api\V1\SupportMessageResource;
use App\Models\AdminProAssignment;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportConversationController extends Controller
{
    public function __construct(private readonly FcmPushService $pushService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeSupportAccess($request);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['open', 'waiting_user', 'closed'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $isAdminPro = $request->user()->isAdminPro();

        $conversations = SupportConversation::query()
            ->when(! $isAdminPro, fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->with(['latestMessage.user:id,name'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->whereNull('read_at')
                ->where('sender_type', $isAdminPro ? 'user' : 'admin_pro')])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 50);

        return SupportConversationResource::collection($conversations);
    }

    public function store(Request $request): SupportConversationResource
    {
        abort_unless(in_array($request->user()->role, ['editor', 'content'], true), 403,
            'Створювати звернення можуть редактори та контент-менеджери.');
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:3', 'max:160'],
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $conversation = DB::transaction(function () use ($request, $data): SupportConversation {
            $conversation = SupportConversation::query()->create([
                'user_id' => $request->user()->id,
                'requester_name' => $request->user()->name,
                'requester_email' => $request->user()->email,
                'subject' => $data['subject'],
                'status' => 'open',
                'last_message_at' => now(),
            ]);
            $conversation->messages()->create([
                'user_id' => $request->user()->id,
                'sender_type' => 'user',
                'body' => $data['message'],
            ]);

            return $conversation;
        });

        system_log('create_support_conversation', 'Створено звернення до підтримки: '.$conversation->subject);
        $this->notifyAdminPro($conversation, 'Нове звернення до підтримки', $request->user()->name.': '.$data['subject']);

        return new SupportConversationResource($this->loadConversation($conversation, $request->user()));
    }

    public function show(Request $request, SupportConversation $supportConversation): SupportConversationResource
    {
        $this->authorizeConversation($request, $supportConversation);
        $viewerType = $request->user()->isAdminPro() ? 'user' : 'admin_pro';
        $supportConversation->messages()
            ->where('sender_type', $viewerType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return new SupportConversationResource($this->loadConversation($supportConversation, $request->user()));
    }

    public function storeMessage(Request $request, SupportConversation $supportConversation): SupportMessageResource
    {
        $this->authorizeConversation($request, $supportConversation);
        abort_if($supportConversation->status === 'closed', 422, 'Закрите звернення не можна доповнювати.');
        $data = $request->validate(['message' => ['required', 'string', 'min:1', 'max:5000']]);
        $isAdminPro = $request->user()->isAdminPro();

        $message = DB::transaction(function () use ($request, $supportConversation, $data, $isAdminPro): SupportMessage {
            $message = $supportConversation->messages()->create([
                'user_id' => $request->user()->id,
                'sender_type' => $isAdminPro ? 'admin_pro' : 'user',
                'body' => $data['message'],
            ]);
            $supportConversation->update([
                'status' => $isAdminPro ? 'waiting_user' : 'open',
                'last_message_at' => now(),
                'closed_at' => null,
            ]);

            return $message;
        });

        system_log('create_support_message', ($isAdminPro ? 'Відповідь AdminPro' : 'Повідомлення користувача').
            ' у зверненні #'.$supportConversation->id);

        if ($isAdminPro) {
            if ($supportConversation->user instanceof User) {
                $this->pushService->sendToUser(
                    $supportConversation->user,
                    'Відповідь служби підтримки',
                    str($data['message'])->stripTags()->limit(180)->toString(),
                    ['type' => 'support_message', 'conversation_id' => (string) $supportConversation->id],
                );
            }
        } else {
            $this->notifyAdminPro($supportConversation, 'Нове повідомлення у підтримці', $supportConversation->subject);
        }

        return new SupportMessageResource($message->load('user:id,name'));
    }

    public function updateStatus(Request $request, SupportConversation $supportConversation): JsonResponse
    {
        abort_unless($request->user()->isAdminPro(), 403, 'Статус звернень змінює лише AdminPro.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'waiting_user', 'closed'])],
        ]);
        $supportConversation->update([
            'status' => $data['status'],
            'closed_at' => $data['status'] === 'closed' ? now() : null,
        ]);
        system_log('update_support_status', 'Змінено статус звернення #'.$supportConversation->id.' на '.$data['status']);

        return response()->json(['message' => 'Статус звернення оновлено.']);
    }

    private function authorizeSupportAccess(Request $request): void
    {
        abort_unless($request->user()->isAdminPro() || in_array($request->user()->role, ['editor', 'content'], true), 403,
            'Розділ підтримки недоступний для звичайних адміністраторів.');
    }

    private function authorizeConversation(Request $request, SupportConversation $conversation): void
    {
        $this->authorizeSupportAccess($request);
        abort_unless($request->user()->isAdminPro() || $conversation->user_id === $request->user()->id, 403,
            'Це звернення належить іншому користувачу.');
    }

    private function loadConversation(SupportConversation $conversation, User $viewer): SupportConversation
    {
        $conversation->load(['messages.user:id,name', 'latestMessage.user:id,name']);
        $conversation->setAttribute('unread_count', $conversation->messages
            ->whereNull('read_at')
            ->where('sender_type', $viewer->isAdminPro() ? 'user' : 'admin_pro')
            ->count());

        return $conversation;
    }

    private function notifyAdminPro(SupportConversation $conversation, string $title, string $body): void
    {
        $adminProId = AdminProAssignment::currentUserId();
        $adminPro = $adminProId ? User::query()->find($adminProId) : null;
        if ($adminPro) {
            $this->pushService->sendToUser($adminPro, $title, $body, [
                'type' => 'support_message',
                'conversation_id' => (string) $conversation->id,
            ]);
        }
    }
}
