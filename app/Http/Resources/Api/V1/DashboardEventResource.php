<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registrationsCount = (int) $this->registrations_count;
        $maxParticipants = $this->max_participants;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'event_date' => $this->event_date?->toIso8601String(),
            'status' => $this->status->value,
            'author' => $this->user?->name,
            'registration_enabled' => (bool) $this->has_registration_button,
            'registrations_count' => $registrationsCount,
            'max_participants' => $maxParticipants,
            'available_slots' => $maxParticipants === null
                ? null
                : max(0, $maxParticipants - $registrationsCount),
        ];
    }
}
