<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\RolesAndPermisssions\PermissionController;
use App\Http\Controllers\Api\V1\RolesAndPermisssions\RoleController;
use App\Http\Controllers\Api\V1\Users\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 *  AuthController <<login ,Register ,Logout >>
 */
Route::prefix("/v1")
    ->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum'])->name('logout');


Route::middleware(['auth:sanctum', 'can:admin-job'])
    ->prefix('/v1/admin')
    ->group(function () {

        /**
         *  RoleController <<assignRolesToUser,index,revokeRolesFromUser,syncRolesToUser>>
         */
        Route::post('/assign-roles/to-user/{user_id}', [RoleController::class, 'assignRolesToUser'])->name('assign.roles');
        Route::get('/get-all-roles', [RoleController::class, 'index'])->name('all.roles');
        Route::post('/remove-roles/from-user/{user_id}', [RoleController::class, 'revokeRolesFromUser'])->name('remove.roles');
        Route::post('/update-roles/to-user/{user_id}', [RoleController::class, 'syncRolesToUser'])->name('update.roles');




        Route::post('/assign-permissions/to-user/{user_id}', [PermissionController::class, 'assignPermissionsToUser'])->name('assign.permissions');
        Route::post('/remove-permissions/from-user/{user_id}', [PermissionController::class, 'revokePermissionsToUser'])->name('remove.permissions');
        Route::post('/update-permissions/to-user/{user_id}', [PermissionController::class, 'syncPermissionsToUser'])->name('update.permissions');
        Route::get('/get-all-permissions', [PermissionController::class, 'index'])->name('all.permissions');

        Route::post('/make-permission', [PermissionController::class, 'makeNewPermission'])->name('make.permission');
        Route::delete('/delete/permission/{permission}', [PermissionController::class, 'destroy'])->name('delete.permission');
        Route::put('/update/permission/{permission}',[PermissionController::class,'updatePermission'])->name('update.permission');
        //role permissions
        Route::post('/assign-permissions/to/role/{role}', [PermissionController::class, 'assignPermissionToRole'])->name('assign.permissions.to.role');
        Route::post('/revoke-permission/to-role/{role}', [PermissionController::class, 'revokePermissionsToRole'])->name('revoke.permissions.role');
        Route::post('update-permissions/to-role/{role}', [PermissionController::class, 'updatePermissionForRole'])->name('update.permissions.role');


        //user-managment
        Route::get('/get-all/users',[UserController::class,'index'])->name('all.users');
        Route::get('/get-user/{user}',[UserController::class,'show'])->name('get.user');
        Route::post('/update/user/{user}',[UserController::class,'update'])->name('update.user');
        Route::delete('/delete/user/{user}',[UserController::class,'destroy'])->name('delete.user');
    });
