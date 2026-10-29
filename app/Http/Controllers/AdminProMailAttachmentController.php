<?php

namespace App\Http\Controllers;

use App\Services\AdminProMailboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminProMailAttachmentController extends Controller
{
    public function __invoke(Request $request, AdminProMailboxService $mailbox, int $uid, string $part): Response
    {
        abort_unless($request->user()?->canAccessAdminProMail(), 403, 'Немає доступу до пошти AdminPro.');

        $data = $request->validate([
            'folder' => ['nullable', 'string', 'in:inbox,archive,spam,trash'],
            'inline' => ['nullable', 'boolean'],
        ]);
        $attachment = $mailbox->attachment($uid, $part, $data['folder'] ?? 'inbox');
        $previewable = str_starts_with($attachment['mime_type'], 'image/')
            || in_array($attachment['mime_type'], ['application/pdf', 'text/plain', 'text/csv'], true);
        $inline = (bool) ($data['inline'] ?? false) && $previewable;
        $disposition = $inline ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        $fallback = trim(Str::ascii($attachment['name'])) ?: 'attachment.bin';

        $response = response($attachment['content'], 200, [
            'Content-Type' => $attachment['mime_type'],
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition($disposition, $attachment['name'], $fallback),
        );

        return $response;
    }
}
