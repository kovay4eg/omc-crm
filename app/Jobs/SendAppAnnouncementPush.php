<?php

namespace App\Jobs;

use App\Models\AppAnnouncement;
use App\Services\FcmPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAppAnnouncementPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $announcementId) {}

    public function handle(FcmPushService $fcm): void
    {
        $announcement = AppAnnouncement::query()->find($this->announcementId);
        if (! $announcement || $announcement->push_sent_at || ! $announcement->push_requested_at) {
            return;
        }

        $result = $fcm->sendAnnouncement($announcement);
        if (! $result['skipped']) {
            system_log(
                'send_app_announcement_push',
                "Push оголошення #{$announcement->id}: {$result['sent']} успішно, {$result['failed']} помилок",
            );
        }
    }
}
