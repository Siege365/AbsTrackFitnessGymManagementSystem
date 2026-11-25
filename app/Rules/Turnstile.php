<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('services.turnstile.enabled')) {
            return;
        }

        $secretKey = config('services.turnstile.secret_key');

        if (empty($secretKey)) {
            return;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (!$response->json('success')) {
            $fail('The CAPTCHA verification failed. Please try again.');
        }
    }
}
