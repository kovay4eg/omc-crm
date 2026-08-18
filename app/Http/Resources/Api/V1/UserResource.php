<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_admin_pro' => $this->isAdminPro(),
            'mail_access' => $this->canAccessAdminProMail(),
            'google_connected' => filled($this->google_refresh_token),
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
        ];
    }
}
