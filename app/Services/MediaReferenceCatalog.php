<?php

namespace App\Services;

use App\Models\AppAnnouncement;
use App\Models\CalendarPlan;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryImage;
use App\Models\FooterSetting;
use App\Models\HomepageSetting;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\Statute;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\Schema;

class MediaReferenceCatalog
{
    /** @return array<int, string> */
    public function paths(): array
    {
        $fields = [
            [Department::class, 'photo'],
            [Department::class, 'slider_photos'],
            [Employee::class, 'photo'],
            [Event::class, 'image'],
            [Event::class, 'smm_image'],
            [EventSummary::class, 'smm_image'],
            [EventSummaryImage::class, 'image'],
            [AppAnnouncement::class, 'image_path'],
            [HomepageSetting::class, 'banner_image'],
            [HomepageSetting::class, 'mobile_banner_image'],
            [HomepageSetting::class, 'logo'],
            [HomepageSetting::class, 'smm_image'],
            [SiteSetting::class, 'team_banner'],
            [FooterSetting::class, 'partner_logos'],
            [CalendarPlan::class, 'file'],
            [Report::class, 'file'],
            [Statute::class, 'file'],
        ];

        $paths = [];

        foreach ($fields as [$modelClass, $field]) {
            $model = new $modelClass;

            if (! Schema::hasTable($model->getTable()) || ! Schema::hasColumn($model->getTable(), $field)) {
                continue;
            }

            foreach ($modelClass::query()->pluck($field) as $value) {
                $paths = [...$paths, ...MediaStorage::paths($value)];
            }
        }

        return array_values(array_unique($paths));
    }

    /** @return array<int, array{disk: string, prefix: string}> */
    public function storageRoots(): array
    {
        return [
            ['disk' => 'public', 'prefix' => ''],
            ['disk' => 'local', 'prefix' => ''],
            ['disk' => 'local', 'prefix' => 'private/'],
        ];
    }
}
