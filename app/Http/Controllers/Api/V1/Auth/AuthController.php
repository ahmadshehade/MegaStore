<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enum\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Summary of register
     * @param \App\Http\Requests\Api\V1\Auth\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {

        try {
            DB::beginTransaction();
             $data=$request->validated();
             $data["password"]=Hash::make($data["password"]);
            $user = User::create($data);
            $user->assignRole(UserRoles::User->value);
            $token = $user->createToken('auth_user')->plainTextToken;
            DB::commit();
            return $this->SuccessMessage(['user' => $user->load('roles'), 'token' => $token], 'Successfully Add New User .', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ErrorMessage([$e->getMessage()], 'error', 500);
        }
    }

    /**
     * Summary of login
     * @param \App\Http\Requests\Api\V1\Auth\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->validated()['email'])->first();
            if (!$user || !Hash::check($request->validated('password'), $user->password)) {
                return $this->ErrorMessage([
                    'email' => $request->validated('email'),
                ], 'error', 500);
            }
            $token = $user->createToken('auth_login')->plainTextToken;
            return $this->SuccessMessage(['user' => $user->load('roles'), 'token' => $token], 'Successfully User Login .', 200);
        } catch (Exception $e) {
            return $this->ErrorMessage([$e->getMessage()], 'error', 500);
        }
    }

    /**
     * Summary of logout
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {

        $user = $request->user();
        $user->tokens()->delete();
        return $this->SuccessMessage(['user' => $user], 'Successfully User Logout', 200);
    }
}
