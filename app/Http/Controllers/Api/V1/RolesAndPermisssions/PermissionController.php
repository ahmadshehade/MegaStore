<?php

namespace App\Http\Controllers\Api\V1\RolesAndPermisssions;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Throwable;

class PermissionController extends Controller
{


    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public   function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        $permissions = $query->orderBy('created_at', 'asc')->get();
        return $this->SuccessMessage([
            'data' => $permissions,
            'count' => $permissions->count(),
        ], 'Successfully fetched all permissions.', 200);
    }


    /**
     * Summary of assignPermissionsToUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissionsToUser(Request $request, $user_id)
    {
        $validator = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->ErrorMessage([
                'errors' => $validator->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        $data = $validator->validated();
        $permissionIds = $data['permission_ids'] ?? [];

        $permissions = Permission::whereIn('id', $permissionIds)->get();

        if ($permissions->isEmpty()) {
            return $this->ErrorMessage([
                'errors' => ['permissions' => ['No permissions found for given ids']],
            ], 'Not Found', 404);
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($user_id);
            $permissionNames = $permissions->pluck('name')->toArray();
            $user->givePermissionTo($permissionNames);
            DB::commit();
            $user->load('permissions');
            return $this->SuccessMessage([
                'data' => $user,
            ], 'Successfully added permissions to user.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('assignPermissionsToUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'errors' => ['message' => 'Server error'],
            ], 'Fail to assign permissions to user', 500);
        }
    }


    /**
     * Summary of revokePermissionsToUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePermissionsToUser(Request $request, $user_id)
    {

        $validator = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->ErrorMessage([
                'errors' => $validator->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        $data = $validator->validated();
        $permissionIdsToRevoke = $data['permission_ids'] ?? [];
        $user = User::findOrFail($user_id);
        $userPermissionIds = $user->permissions()->pluck('id')->toArray();
        $permissions = Permission::whereIn('id', $permissionIdsToRevoke)->get();
        if ($permissions->isEmpty()) {
            return $this->ErrorMessage([
                'errors' => ['permissions' => ['No permissions found for given ids']],
            ], 'Not Found', 404);
        }
        $notAssigned = array_values(array_diff($permissionIdsToRevoke, $userPermissionIds));
        if (!empty($notAssigned)) {
            return $this->ErrorMessage([
                'errors' => [
                    'permissions' => ['The following permission ids are not assigned to the user: ' . implode(', ', $notAssigned)]
                ]
            ], 'Validation Error', 422);
        }
        DB::beginTransaction();
        try {
            $permissionNames = $permissions->pluck('name')->toArray();
            foreach ($permissionNames as $permName) {
                $user->revokePermissionTo($permName);
            }
            DB::commit();
            $user->load('permissions');
            return $this->SuccessMessage([
                'data' => $user,
            ], 'Successfully revoked permissions from user.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('revokePermissionsToUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'errors' => ['message' => 'Server error']
            ], 'Fail Revoke Permissions For User.', 500);
        }
    }

    /**
     * Summary of syncPermissionsToUser
     * @param \Illuminate\Http\Request $request
     * @param mixed $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncPermissionsToUser(Request $request, $user_id)
    {
        $validator = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
        if ($validator->fails()) {
            return $this->ErrorMessage([
                'errors' => $validator->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        $data = $validator->validated();
        $permissionIds = $data['permission_ids'] ?? [];
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        if ($permissions->isEmpty()) {
            return $this->ErrorMessage([
                'errors' => ['permissions' => ['No permissions found for given ids']],
            ], 'Not Found', 404);
        }
        $user = User::findOrFail($user_id);
        DB::beginTransaction();
        try {
            $permissionNames = $permissions->pluck('name')->toArray();
            $user->syncPermissions($permissionNames);
            DB::commit();
            $user->load('permissions');
            return $this->SuccessMessage([
                'data' => $user,
            ], 'Successfully synced permissions for user.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('syncPermissionsToUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'errors' => ['message' => 'Server error'],
            ], 'Fail To Sync Permissions For User.', 500);
        }
    }


    /**
     * Summary of makeNewPermission
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeNewPermission(Request $request)
    {
        $validator = Validator::make($request->only('name'), [
            'name' => ['required', 'string', 'min:3', 'max:13', 'unique:permissions']
        ]);
        if ($validator->fails()) {
            return $this->ErrorMessage([
                'errors' => $validator->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        try {
            DB::beginTransaction();
            $data = $validator->validated();
            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => 'web'
            ]);
            DB::commit();
            return $this->SuccessMessage([
                'data' => $permission,
            ], 'Successfully Make New User .', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fial Make Permission ' . $e->getMessage());
            return $this->ErrorMessage([
                'errors' => 'Fail To Make Permission .',
            ], 'Internal Server Error .', 500);
        }
    }


    /**
     * Summary of destroy
     * @param \Spatie\Permission\Models\Permission $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return $this->SuccessMessage([
            'data' => true,
        ], 'Successfully Delete Permission ', 200);
    }


    //Roles And Permission

    /**
     * Summary of assignPermissionToRole
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissionToRole(Request $request, Role $role)
    {
        $validation = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
        if ($validation->fails()) {
            return $this->ErrorMessage([
                'errors' => $validation->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        try {
            DB::beginTransaction();
            $data = $validation->validated();
            $permisssion_ids = $data['permission_ids'];
            $permissions = Permission::whereIn('id', $permisssion_ids)->get();
            if ($permissions->isEmpty()) {
                return $this->ErrorMessage([
                    'errors' => ['permissions' => ['No permissions found for given ids']],
                ], 'Not Found', 404);
            }
            $role->givePermissionTo($permissions);
            DB::commit();
            return $this->SuccessMessage([
                'data' => $role->load('permissions')
            ], 'Successfully Assign Permissions To Role .', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('assignPermissionToRole error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'error' => $e->getMessage(),
            ], 'Fail To Assign Permission To Role ', 500);
        }
    }


    /**
     * Summary of revokePermissionsToRole
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePermissionsToRole(Request $request, Role $role)
    {

        $validation = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
        if ($validation->fails()) {
            return $this->ErrorMessage([
                'errors' => $validation->errors()->toArray(),
            ], 'Validation Failed', 422);
        }
        $data = $validation->validated();
        $permission_ids = $data['permission_ids'] ?? [];
        try {
            DB::beginTransaction();
            $permissions = Permission::whereIn('id', $permission_ids)->get();
            if ($permissions->isEmpty()) {
                return $this->ErrorMessage([
                    'errors' => ['permissions' => ['No permissions found for given ids']],
                ], 'Not Found', 404);
            }
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
            DB::commit();
            return $this->SuccessMessage([
                'data' => $role->load('permissions'),
            ], 'Successfully revoked permissions from role.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('revokePermissionsToRole error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'errors' => ['message' => 'Server error'],
            ], 'Fail to revoke permissions from role.', 500);
        }
    }


    /**
     * Summary of updatePermissionForRole
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePermissionForRole(Request $request, Role $role)
    {
        $validation = Validator::make($request->only('permission_ids'), [
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
        if ($validation->fails()) {
            return $this->ErrorMessage([
                'errors' => $validation->errors()->toArray(),
            ], 'Validation Failed', 422);
        }

        $data = $validation->validated();
        $permission_ids = array_values($data['permission_ids'] ?? []);
        try {
            DB::beginTransaction();
            $permissions = Permission::whereIn('id', $permission_ids)->get();
            $foundIds = $permissions->pluck('id')->toArray();
            $missing = array_values(array_diff($permission_ids, $foundIds));
            if (!empty($missing)) {
                DB::rollBack();
                return $this->ErrorMessage([
                    'errors' => [
                        'permissions' => ['Permissions not found for ids: ' . implode(', ', $missing)]
                    ]
                ], 'Not Found', 404);
            }
            $role->syncPermissions($permissions);
            DB::commit();
            return $this->SuccessMessage([
                'data' => $role->load('permissions'),
            ], 'Successfully updated permissions for role.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('updatePermissionForRole error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->ErrorMessage([
                'errors' => ['message' => 'Server error'],
            ], 'Failed to update permissions for role.', 500);
        }
    }


    /**
     * Summary of updatePermission
     * @param \Spatie\Permission\Models\Permission $permission
     * @return \Illuminate\Http\JsonResponse
     */
public function updatePermission(Request $request, Permission $permission)
{
    $input = $request->only('name');

    $validator = Validator::make($input, [
        'name' => [
            'required',
            'string',
            'min:3',
            'max:13',
            Rule::unique('permissions')->ignore($permission->id),
        ],
    ]);

    if ($validator->fails()) {
        return $this->ErrorMessage([
            'errors' => $validator->errors()->toArray(),
        ], 'Validation Failed', 422);
    }
    $data = $validator->validated();
    try {
        DB::beginTransaction();
        $name = trim($data['name']);

        $permission->update([
            'name'       => $name,
            'guard_name' => 'web',
        ]);
        DB::commit();
        $permission->refresh();
        return $this->SuccessMessage([
            'data' => $permission,
        ], 'Successfully updated permission.', 200);
    } catch (Throwable $e) {
        DB::rollBack();
        Log::error('Failed to update permission: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'permission_id' => $permission->id,
        ]);

        return $this->ErrorMessage([
            'errors' => ['message' => 'Server error'],
        ], 'Failed to update permission.', 500);
    }
}
}
