<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class Recaptcha
{
    public function ready(): bool
    {
        return filled(config('recaptcha.site_key')) && filled(config('recaptcha.secret_key')) && count(config('recaptcha.hostnames', [])) > 0;
    }

    public function validate(Request $request): void
    {
        if (! $this->ready()) {
            throw ValidationException::withMessages(['captcha' => 'Проверка безопасности временно недоступна. Попробуйте позже.']);
        }
        $request->validate(['g-recaptcha-response' => 'required|string|max:4096'], ['g-recaptcha-response.required' => 'Подтвердите, что вы не робот.']);
        try {
            $response = Http::asForm()->timeout(8)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret_key'), 'response' => $request->input('g-recaptcha-response'),
            ]);
            $valid = $response->successful() && $response->json('success') === true && in_array($response->json('hostname'), config('recaptcha.hostnames'), true);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['captcha' => 'Не удалось проверить reCAPTCHA. Попробуйте ещё раз.']);
        }
        if (! $valid) {
            throw ValidationException::withMessages(['captcha' => 'Проверка reCAPTCHA не пройдена или истекла. Пройдите её заново.']);
        }
    }
}
