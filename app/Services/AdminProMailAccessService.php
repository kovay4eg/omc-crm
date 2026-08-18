<?php

namespace App\Services;

use App\Models\AdminProMailAccess;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AdminProMailAccessService
{
    /**
     * @throws AuthorizationException
     */
    public function setAccess(User $actor, User $target, bool $enabled): void
    {
        throw_unless(
            $actor->isAdminPro(),
            AuthorizationException::class,
            'Лише AdminPro може надавати доступ до пошти.',
        );

        if ($target->isAdminPro()) {
            return;
        }

        if ($enabled) {
            AdminProMailAccess::query()->updateOrCreate(
                ['user_id' => $target->getKey()],
                ['granted_by' => $actor->getKey()],
            );
        } else {
            AdminProMailAccess::query()->where('user_id', $target->getKey())->delete();
        }

        system_log(
            $enabled ? 'admin_pro_mail_access_granted' : 'admin_pro_mail_access_revoked',
            ($enabled ? 'Надано' : 'Відкликано').' повний доступ до пошти для '.$target->email.'.',
        );
    }
}
