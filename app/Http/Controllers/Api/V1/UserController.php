<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AdminProAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAdmin($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $adminProId = AdminProAssignment::currentUserId();

        return UserResource::collection(User::query()
            ->when($adminProId, fn ($query) => $query->where('id', '!=', $adminProId))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate($filters['per_page'] ?? 50));
    }

    public function store(Request $request): UserResource
    {
        $this->authorizeAdmin($request);
        $user = User::query()->create($request->validate($this->rules()));
        system_log('create_user', 'Створено користувача CRM: '.$user->email);

        return new UserResource($user);
    }

    public function update(Request $request, User $user): UserResource
    {
        $this->authorizeAdmin($request);
        abort_if($user->isAdminPro(), 403, 'Обліковий запис AdminPro змінюється лише через його профіль.');
        $data = $request->validate($this->rules($user));
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);
        system_log('update_user', 'Оновлено користувача CRM: '.$user->email);

        return new UserResource($user->refresh());
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_if($user->isAdminPro(), 403, 'Обліковий запис AdminPro не можна видалити.');
        abort_if($request->user()->is($user), 422, 'Не можна видалити власний обліковий запис.');
        abort_if($user->isAdmin() && User::query()->where('role', 'admin')->count() <= 1, 422, 'Не можна видалити останнього адміністратора.');

        $email = $user->email;
        $user->delete();
        system_log('delete_user', 'Видалено користувача CRM: '.$email);

        return response()->json(['message' => 'Користувача видалено.']);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'editor', 'content'])],
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403, 'Керування користувачами доступне лише адміністраторам.');
    }
}
