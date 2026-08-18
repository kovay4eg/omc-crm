<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AdminProMailboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProMailController extends Controller
{
    public function __construct(private readonly AdminProMailboxService $mailbox) {}

    public function status(Request $request): JsonResponse
    {
        $this->authorizeAdminPro($request);

        $status = $this->mailbox->status();
        $status['folders'] = $status['configured'] ? $this->mailbox->folders() : [];

        return $this->privateResponse(['data' => $status]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:120'],
            'folder' => ['nullable', 'string', 'in:inbox,archive,spam,trash'],
            'filter' => ['nullable', 'string', 'in:all,unread,starred'],
        ]);

        return $this->privateResponse($this->mailbox->messages(
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 30),
            $data['search'] ?? null,
            $data['folder'] ?? 'inbox',
            $data['filter'] ?? 'all',
        ));
    }

    public function show(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);

        $data = $request->validate(['folder' => ['nullable', 'string', 'in:inbox,archive,spam,trash']]);

        return $this->privateResponse(['data' => $this->mailbox->message($uid, $data['folder'] ?? 'inbox')]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate([
            'to' => ['required', 'array', 'min:1', 'max:10'],
            'to.*' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:50000'],
        ]);

        $this->mailbox->send(array_values(array_unique($data['to'])), $data['subject'], $data['body']);
        system_log('admin_pro_mail_send', 'Користувач із поштовим доступом надіслав лист через post@omc.pl.ua.');

        return $this->privateResponse(['message' => 'Лист надіслано.'], 201);
    }

    public function update(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate([
            'folder' => ['nullable', 'string', 'in:inbox,archive,spam,trash'],
            'read' => ['sometimes', 'boolean'],
            'flagged' => ['sometimes', 'boolean'],
            'move_to' => ['sometimes', 'string', 'in:inbox,archive,spam,trash'],
        ]);
        abort_unless(
            array_key_exists('read', $data) || array_key_exists('flagged', $data) || array_key_exists('move_to', $data),
            422,
            'Не вказано дію над листом.',
        );
        $folder = $data['folder'] ?? 'inbox';
        if (array_key_exists('read', $data)) {
            $this->mailbox->mark($uid, (bool) $data['read'], $folder);
        }
        if (array_key_exists('flagged', $data)) {
            $this->mailbox->flag($uid, (bool) $data['flagged'], $folder);
        }
        if (isset($data['move_to'])) {
            $this->mailbox->move($uid, $data['move_to'], $folder);
        }

        return $this->privateResponse(['message' => 'Стан листа оновлено.']);
    }

    public function destroy(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate([
            'folder' => ['nullable', 'string', 'in:inbox,archive,spam,trash'],
            'permanently' => ['nullable', 'boolean'],
        ]);
        $this->mailbox->delete($uid, $data['folder'] ?? 'inbox', (bool) ($data['permanently'] ?? false));
        system_log('admin_pro_mail_delete', 'Користувач із поштовим доступом видалив лист зі скриньки post@omc.pl.ua.');

        return $this->privateResponse(['message' => 'Лист видалено.']);
    }

    private function authorizeAdminPro(Request $request): void
    {
        abort_unless($request->user()?->canAccessAdminProMail(), 403, 'Немає доступу до пошти AdminPro.');
    }

    private function privateResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
