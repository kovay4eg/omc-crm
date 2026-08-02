<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SystemLogResource;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->isAdmin(), 403, 'Журнал дій доступний лише адміністраторам.');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'in:web,mobile_app'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return SystemLogResource::collection(SystemLog::query()
            ->with('user:id,name,email')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))))
            ->when($filters['source'] ?? null, fn ($query, $source) => $query->where('source', $source))
            ->latest()
            ->paginate($filters['per_page'] ?? 50)
            ->withQueryString());
    }
}
