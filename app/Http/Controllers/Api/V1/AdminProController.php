<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdminProAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminProController extends Controller
{
    public function transfer(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminPro(), 403, 'Передавати права може лише поточний AdminPro.');
        $data = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'string', 'max:255'],
        ]);
        abort_unless(Hash::check($data['current_password'], $request->user()->password), 422,
            'Поточний пароль введено неправильно.');

        $target = User::query()->findOrFail($data['target_user_id']);
        abort_if($target->is($request->user()), 422, 'Оберіть інший обліковий запис.');
        abort_unless($target->role === 'admin', 422, 'Передати AdminPro можна лише адміністратору.');

        DB::transaction(fn () => AdminProAssignment::transferTo($target));
        system_log('transfer_admin_pro', 'Права AdminPro передано користувачу '.$target->email);

        return response()->json([
            'message' => 'Права AdminPro передано. Поточний сеанс потрібно завершити.',
        ]);
    }
}
