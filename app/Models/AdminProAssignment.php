<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminProAssignment extends Model
{
    public const INITIAL_EMAIL = 'koshevoiy777@gmail.com';

    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function currentUserId(): ?int
    {
        $assignedId = static::query()->whereKey(1)->value('user_id');
        if ($assignedId !== null) {
            return (int) $assignedId;
        }

        $initialId = User::query()
            ->whereRaw('LOWER(email) = ?', [self::INITIAL_EMAIL])
            ->where('role', 'admin')
            ->value('id');

        return $initialId === null ? null : (int) $initialId;
    }

    public static function transferTo(User $user): void
    {
        static::query()->updateOrCreate(['id' => 1], ['user_id' => $user->id]);
    }
}
