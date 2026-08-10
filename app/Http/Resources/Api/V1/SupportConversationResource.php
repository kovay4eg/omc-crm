<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status,
            'requester' => [
                'name' => $this->requester_name,
                'email' => $this->requester_email,
            ],
            'unread_count' => (int) ($this->unread_count ?? 0),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'latest_message' => $this->whenLoaded(
                'latestMessage',
                fn () => $this->latestMessage ? new SupportMessageResource($this->latestMessage) : null,
            ),
            'messages' => SupportMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
