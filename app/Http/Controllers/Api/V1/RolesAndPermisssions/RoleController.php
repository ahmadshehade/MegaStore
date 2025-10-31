<?php

namespace App\Http\Controllers\Api\V1\RolesAndPermisssions;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Throwable;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{

    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Role::query();
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $roles = $query->orderBy('created_at', 'asc')->get();

        return $this->SuccessMessage([
            'data' => $roles,
            'count' => $roles->count(),
        ], 'Successfully fetched all roles.', 200);
    }



    /**
     * Summary of assignRolesToUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $user_id
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRolesToUser(Request $request, $user_id)
    {
        $validator = Validator::make($request->only('role_ids'), [
            'role_ids'   => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);
        if ($validator->fails()) {

            throw new HttpResponseException(response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()->toArray(),
            ], 422));
        }
        $data = $validator->validated();
        $roleIds = $data['role_ids'] ?? [];
        $roles = Role::whereIn('id', $roleIds)->get();
        if ($roles->isEmpty()) {
            return response()->json([
                'message' => 'No roles found for given ids',
                'errors'  => ['roles' => ['No roles found for given ids']],
            ], 404);
        }
        DB::beginTransaction();
        try {
            $user = User::findOrFail($user_id);
            $user->assignRole($roles->pluck('name')->toArray());
            DB::commit();
            $user->load('roles');
            return $this->SuccessMessage([
                'user' => $user
            ], 'Successfully assigned roles to user.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('assignRolesToUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'error' => $e->getMessage(),
            ], 'Server Error', 500);
        }
    }


    /**
     * Summary of revokeRolesFromUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $userId
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeRolesFromUser(Request $request, $userId)
    {
        $validator = Validator::make($request->only('role_ids'), [
            'role_ids'   => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()->toArray(),
            ], 422));
        }

        $data = $validator->validated();
        $roleIdsToRevoke = $data['role_ids'] ?? [];

        $user = User::findOrFail($userId);
        $userRoleIds = $user->roles->pluck('id')->toArray();

        $roles = Role::whereIn('id', $roleIdsToRevoke)->get();
        if ($roles->isEmpty()) {
            return $this->ErrorMessage([
                'errors'  => [
                    'roles' => ['No roles found for given ids'],
                ]
            ], 'No roles found for given ids', 404);
        }

        $notAssigned = array_values(array_diff($roleIdsToRevoke, $userRoleIds));
        if (!empty($notAssigned)) {
            return $this->ErrorMessage([
                'errors'  => [
                    'errors'  => ['roles' => ['The following role ids are not assigned to the user: ' . implode(', ', $notAssigned)]],
                ]
            ], 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $roleNamesToRevoke = $roles->pluck('name')->toArray();
            foreach ($roleNamesToRevoke as $roleName) {
                $user->removeRole($roleName);
            }
            DB::commit();
            $user->load('roles');
            return $this->SuccessMessage([
              'user' => $user,
            ],'Successfully revoked roles from user.',200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('revokeRolesFromUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return  $this->ErrorMessage([
                'error'=> $e->getMessage(),
            ],'Server error',500);
         
        }
    }


    /**
     * Summary of syncRolesToUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $user_id
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncRolesToUser(Request $request, $user_id)
    {
        $validator = Validator::make($request->only('role_ids'), [
            'role_ids'   => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()->toArray(),
            ], 422));
        }

        $data = $validator->validated();
        $roleIds = $data['role_ids'] ?? [];
        $roles = Role::whereIn('id', $roleIds)->get();

        if ($roles->isEmpty()) {
            return response()->json([
                'message' => 'No roles found for given ids',
                'errors'  => ['roles' => ['No roles found for given ids']],
            ], 404);
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($user_id);
            $user->syncRoles($roles->pluck('name')->toArray());
            DB::commit();

            $user->load('roles');
            return response()->json([
                'message' => 'Successfully synced roles to user.',
                'data'    => ['user' => $user]
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('syncRolesToUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
