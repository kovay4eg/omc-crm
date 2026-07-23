<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\HomepageSetting;
use App\Support\MediaStorage;
use Illuminate\Http\Response;

class EventShareImageController extends Controller
{
    /**
     * Повертає JPEG для Open Graph. Telegram та частина інших соцмереж не
     * підтримують AVIF у прев'ю, тому конвертація відбувається на льоту.
     */
    public function show(Event $event): Response
    {
        abort_unless(
            $event->event_date->gte(today())
                && in_array($event->status, [
                    EventStatus::Published,
                    EventStatus::Rescheduled,
                    EventStatus::Cancelled,
                ], true),
            404,
        );

        $settings = HomepageSetting::first();
        $sourcePath = $event->smm_image
            ?: $event->image
            ?: $settings?->smm_image
            ?: $settings?->banner_image
            ?: $settings?->logo;

        abort_if(blank($sourcePath), 404);

        $media = MediaStorage::find($sourcePath);

        abort_if($media === null, 404);

        $source = @file_get_contents($media['absolute_path']);
        $image = $source === false ? false : @imagecreatefromstring($source);

        abort_if($image === false, 404);

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);

        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imageinterlace($canvas, true);

        ob_start();
        imagejpeg($canvas, null, 90);
        $jpeg = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($image);

        abort_if($jpeg === false, 404);

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Length' => (string) strlen($jpeg),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
