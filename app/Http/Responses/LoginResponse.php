<?php
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            
            $challengeToken = bin2hex(random_bytes(20));
            Cache::put('twofactor:' . $challengeToken, $user->id, 300); // 5 دقائق

            return new JsonResponse([
                'two_factor' => true,
                'challenge_token' => $challengeToken,
                'message' => 'Two factor authentication required.'
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return new JsonResponse([
            'two_factor' => false,
            'token' => $token,
            'user' => $user->only(['id','name','email']),
        ]);
    }
}
