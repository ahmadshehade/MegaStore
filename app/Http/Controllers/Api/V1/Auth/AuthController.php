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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data["password"] = Hash::make($data["password"]);
            $user = User::create($data);
            $token = $user->createToken('auth_user')->plainTextToken;
            $user->assignRole(UserRoles::User->value);
            DB::commit();
            return response()->json(['user' => $user, 'token' => $token, 'message' => 'Successfully Add New User .'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Register error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error.'], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
            }

            if ($user->two_factor_secret) {
                $challengeToken = bin2hex(random_bytes(20));
                Cache::put('twofactor:challenge:' . $challengeToken, $user->id, now()->addMinutes(5));
                return response()->json([
                    'two_factor' => true,
                    'challenge_token' => $challengeToken,
                    'message' => 'Two factor authentication required.'
                ], 200);
            }

            $token = $user->createToken('api-token')->plainTextToken;
            $user->update(['is_active' => true]);

            return response()->json([
                'two_factor' => false,
                'token' => $token,
                'user' => $user->only(['id', 'name', 'email']),
            ], 200);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error.'], 500);
        }
    }

    public function twoFactorChallenge(Request $request)
    {
        $request->validate([
            'challenge_token' => 'required|string',
            'code' => 'required|string',
        ]);

        $token = $request->challenge_token;
        $userId = Cache::get('twofactor:challenge:' . $token);

        if (! $userId) return response()->json(['message' => 'Invalid or expired challenge token.'], 422);

        $user = User::find($userId);
        if (! $user || ! $user->two_factor_secret) return response()->json(['message' => 'User not found or 2FA not enabled.'], 422);

        try {
            $secret = Crypt::decryptString($user->two_factor_secret);
            $inputCode = str_replace([' ', '-'], '', $request->code);

            $google2fa = new Google2FA();
            $isTotpValid = $google2fa->verifyKey($secret, $inputCode, 5);

            $usedRecovery = false;
            if (! $isTotpValid) {
                // الحل: التحقق من recovery codes بدون إزالة الشرطات
                $stored = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];
                foreach ($stored as $i => $storedCode) {
                    // recovery codes غير مشفرة، استخدم hash_equals مباشرة
                    if (hash_equals($request->code, $storedCode)) {
                        $usedRecovery = true;
                        array_splice($stored, $i, 1);
                        $user->two_factor_recovery_codes = json_encode(array_values($stored));
                        $user->save();
                        break;
                    }
                }
            }

            if (! $isTotpValid && ! $usedRecovery) return response()->json(['message' => 'Invalid 2FA code.'], 422);

            Cache::pull('twofactor:challenge:' . $token);
            $user->tokens()->delete();
            $plainToken = $user->createToken('api-token')->plainTextToken;
            $user->update(['is_active' => true]);

            return response()->json([
                'message' => 'Two factor authentication successful.',
                'token' => $plainToken,
                'user' => $user->only(['id', 'name', 'email']),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('2FA challenge error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal server error.'], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->update(['is_active' => false]);
        return response()->json(['message' => 'Successfully User Logout', 'user' => $user], 200);
    }
}
