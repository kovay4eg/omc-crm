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

        return $this->privateResponse(['data' => $this->mailbox->status()]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        return $this->privateResponse($this->mailbox->messages(
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 30),
            $data['search'] ?? null,
        ));
    }

    public function show(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);

        return $this->privateResponse(['data' => $this->mailbox->message($uid)]);
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
        system_log('admin_pro_mail_send', 'AdminPro надіслав лист через post@omc.pl.ua.');

        return $this->privateResponse(['message' => 'Лист надіслано.'], 201);
    }

    public function update(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $data = $request->validate(['read' => ['required', 'boolean']]);
        $this->mailbox->mark($uid, (bool) $data['read']);

        return $this->privateResponse(['message' => 'Стан листа оновлено.']);
    }

    public function destroy(Request $request, int $uid): JsonResponse
    {
        $this->authorizeAdminPro($request);
        $this->mailbox->delete($uid);
        system_log('admin_pro_mail_delete', 'AdminPro видалив лист зі скриньки post@omc.pl.ua.');

        return $this->privateResponse(['message' => 'Лист видалено.']);
    }

    private function authorizeAdminPro(Request $request): void
    {
        abort_unless($request->user()?->isAdminPro(), 403, 'Пошта доступна лише AdminPro.');
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
