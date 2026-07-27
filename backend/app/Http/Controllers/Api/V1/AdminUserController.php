<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()->with('roles')->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:student,admin,owner'],
        ]);

        if ($data['role'] === 'owner' && ! $request->user()->isOwner()) {
            abort(403, __('auth.forbidden'));
        }

        $user->syncRoles([$data['role']]);

        return response()->json([
            'message' => __('admin.role_updated'),
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'message' => __('admin.user_updated'),
            'data' => new UserResource($user->load('roles')),
        ]);
    }
}
