<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Summary of enable
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        $secret = $this->google2fa->generateSecretKey();
        $company = config('app.name', 'MegaStore');
        $qrUrl = $this->google2fa->getQRCodeUrl($company, $user->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $inlineSvg = $writer->writeString($qrUrl);

        $confirmToken = bin2hex(random_bytes(20));
        Cache::put('twofactor:pending:' . $confirmToken, ['user_id'=>$user->id,'secret'=>$secret], now()->addMinutes(5));

        return response()->json([
            'message'=>'Two factor secret generated.',
            'qr'=>$inlineSvg,
            'confirm_token'=>$confirmToken
        ],200);
    }

    /**
     * Summary of confirm
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'confirm_token'=>'required|string',
            'code'=>'required|string'
        ]);

        $confirmToken = $request->confirm_token;
        $payload = Cache::get('twofactor:pending:'.$confirmToken);

        if (!$payload) return response()->json(['message'=>'Invalid or expired confirm token.'],422);

        $user = $request->user();
        if ($user->id !== $payload['user_id']) return response()->json(['message'=>'Unauthorized.'],403);

        $cleanCode = str_replace([' ','-'],'',$request->code);

        try {
            $verified = $this->google2fa->verifyKey($payload['secret'], $cleanCode, 5);
            if (! $verified) return response()->json(['message'=>'Invalid 2FA code.'],422);

            $user->two_factor_secret = Crypt::encryptString($payload['secret']);
            $recoveryCodes = collect(range(1,8))->map(fn()=>Str::random(10).'-'.Str::random(6))->toArray();
            $user->two_factor_recovery_codes = json_encode($recoveryCodes);
            $user->save();
            Cache::forget('twofactor:pending:'.$confirmToken);

            return response()->json(['message'=>'Two factor enabled.','recovery_codes'=>$recoveryCodes],200);
        } catch (\Throwable $e) {
            Log::error('2FA confirm error: '.$e->getMessage(), ['user_id'=>$user->id]);
            return response()->json(['message'=>'Internal server error.'],500);
        }
    }

    /**
     * Summary of recoveryCodes
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recoveryCodes(Request $request)
    {
        $codes = json_decode($request->user()->two_factor_recovery_codes ?? '[]',true);
        return response()->json(['recovery_codes'=>$codes],200);
    }

    /**
     * Summary of regenerateRecoveryCodes
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate(['password'=>'required|string']);
        $user = $request->user();
        if (! Hash::check($request->password, $user->password)) return response()->json(['message'=>'Invalid password.'],403);

        $recoveryCodes = collect(range(1,8))->map(fn()=>Str::random(10).'-'.Str::random(6))->toArray();
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        return response()->json(['message'=>'Recovery codes regenerated.','recovery_codes'=>$recoveryCodes],200);
    }

    /**
     * Summary of disable
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password'=>'required|string',
            'code'=>'sometimes|string',
            'recovery_code'=>'sometimes|string'
        ]);

        $user = $request->user();
        if (! Hash::check($request->password,$user->password)) return response()->json(['message'=>'Invalid password.'],403);

        $ok = false;

        if ($request->filled('code') && $user->two_factor_secret) {
            try {
                $secret = Crypt::decryptString($user->two_factor_secret);
                $ok = $this->google2fa->verifyKey($secret,str_replace([' ','-'],'',$request->code),5);
            } catch (\Throwable $e) {
                Log::debug('2FA disable verifyKey failed', ['user_id'=>$user->id,'error'=>$e->getMessage()]);
            }
        }

        if (!$ok && $request->filled('recovery_code')) {
            $codes = json_decode($user->two_factor_recovery_codes ?? '[]',true);
            if (is_array($codes)) {
                foreach ($codes as $i=>$c) {
                    if (hash_equals($request->recovery_code,$c)) {
                        array_splice($codes,$i,1);
                        $user->two_factor_recovery_codes = json_encode(array_values($codes));
                        $ok = true;
                        break;
                    }
                }
            }
        }

        if (! $ok) return response()->json(['message'=>'Invalid code or recovery code.'],422);

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json(['message'=>'Two factor authentication disabled.'],200);
    }
}
