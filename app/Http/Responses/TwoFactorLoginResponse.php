<?php
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        $challengeToken = $request->input('challenge_token');
        $code = $request->input('code');

        $userId = Cache::pull('twofactor:' . $challengeToken);

        if (! $userId) {
            return new JsonResponse(['message' => 'Invalid or expired challenge token'], 422);
        }

        $user = \App\Models\User::find($userId);

        $google2fa = new Google2FA();
        if (! $google2fa->verifyKey($user->two_factor_secret, $code)) {
            return new JsonResponse(['message' => 'Invalid 2FA code'], 422);
        }

        // رمز 2FA صحيح → اصدر توكن API
        $token = $user->createToken('api-token')->plainTextToken;

        return new JsonResponse([
            'message' => 'Two factor authentication successful.',
            'token' => $token,
            'user' => $user->only(['id','name','email']),
        ]);
    }
}
