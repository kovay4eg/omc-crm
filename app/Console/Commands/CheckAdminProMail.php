<?php

namespace App\Console\Commands;

use App\Models\AdminProAssignment;
use App\Models\AdminProMailCheckpoint;
use App\Models\User;
use App\Services\AdminProMailboxService;
use App\Services\FcmPushService;
use Illuminate\Console\Command;

class CheckAdminProMail extends Command
{
    protected $signature = 'admin-pro-mail:check';

    protected $description = 'Перевірити нові листи AdminPro та надіслати приватні push-сповіщення';

    public function handle(AdminProMailboxService $mailbox, FcmPushService $push): int
    {
        if (! $mailbox->configured()) {
            $this->components->info('Поштова скринька не налаштована.');

            return self::SUCCESS;
        }

        $checkpoint = AdminProMailCheckpoint::query()->firstOrNew([
            'mailbox' => (string) config('admin_pro_mail.address'),
        ]);
        $batch = $mailbox->newMessageBatch($checkpoint->last_uid, $checkpoint->uid_validity);

        if (! $checkpoint->exists || $batch['reset']) {
            $this->saveCheckpoint($checkpoint, $batch);
            $this->components->info('Початковий стан пошти збережено без повторних сповіщень.');

            return self::SUCCESS;
        }

        if ($batch['messages'] === []) {
            $this->saveCheckpoint($checkpoint, $batch);

            return self::SUCCESS;
        }

        if (! $push->configured()) {
            $this->components->warn('Firebase Cloud Messaging не налаштовано. Нові листи буде перевірено повторно.');

            return self::FAILURE;
        }

        $adminProId = AdminProAssignment::currentUserId();
        $users = User::query()
            ->where(function ($query) use ($adminProId): void {
                if ($adminProId !== null) {
                    $query->whereKey($adminProId)->orWhereHas('adminProMailAccess');
                } else {
                    $query->whereHas('adminProMailAccess');
                }
            })
            ->get();
        $latest = $batch['messages'][0];
        $count = count($batch['messages']);
        $title = $count === 1 ? 'Новий лист від '.$latest['from_name'] : 'Нові листи: '.$count;
        $body = $count === 1 ? $latest['subject'] : 'Відкрийте пошту ОМЦ, щоб переглянути повідомлення.';

        foreach ($users as $user) {
            $push->sendToUser($user, $title, $body, [
                'type' => 'admin_pro_mail',
                'folder' => 'inbox',
                'uid' => (string) $latest['uid'],
            ], 'push_mail');
        }

        $this->saveCheckpoint($checkpoint, $batch);
        system_log('admin_pro_mail_push', 'Надіслано сповіщення про нові листи: '.$count.'.');
        $this->components->info('Сповіщення про нові листи опрацьовано: '.$count.'.');

        return self::SUCCESS;
    }

    private function saveCheckpoint(AdminProMailCheckpoint $checkpoint, array $batch): void
    {
        $checkpoint->fill([
            'uid_validity' => $batch['uid_validity'],
            'last_uid' => $batch['last_uid'],
            'checked_at' => now(),
        ])->save();
    }
}
