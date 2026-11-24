<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Recaptcha;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function __construct(private Recaptcha $recaptcha) {}

    public function form(string $mode): View
    {
        return view('store.auth', [
            'mode' => $mode, 'captchaReady' => $this->recaptcha->ready(),
            'mailReady' => ! in_array(config('mail.default'), ['log', 'array'], true),
        ]);
    }

    public function registerForm(): View
    {
        return $this->form('register');
    }

    public function loginForm(): View
    {
        return $this->form('login');
    }

    public function forgotForm(): View
    {
        return $this->form('forgot');
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalize($request);
        $this->recaptcha->validate($request);
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            throw ValidationException::withMessages(['email' => 'Регистрация временно недоступна. Попробуйте позже.']);
        }
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:254|unique:users,email', 'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()]]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        try {
            event(new Registered($user));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('verification.notice')->with('mail-error', 'Не удалось отправить письмо. Повторите отправку немного позже.');
        }

        return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->normalize($request);
        $request->validate(['email' => 'required|email|max:254', 'password' => 'required|string|max:4096']);
        $key = 'customer-login:'.hash('sha256', $request->input('email').'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Слишком много попыток. Повторите через минуту.']);
        }
        $this->recaptcha->validate($request);
        if (! Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password'), 'is_admin' => false], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Неверный email или пароль.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->hasVerifiedEmail() ? 'account' : 'verification.notice'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store');
    }

    public function verifyNotice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        return view('store.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('account')->with('status', 'Почта подтверждена. Добро пожаловать!');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('mail-error', 'Не удалось отправить письмо. Попробуйте позже.');
        }

        return back()->with('status', 'verification-link-sent');
    }

    public function forgot(Request $request): RedirectResponse
    {
        $this->normalize($request);
        $this->recaptcha->validate($request);
        $request->validate(['email' => 'required|email|max:254']);
        if (User::where('email', $request->input('email'))->where('is_admin', false)->exists()) {
            try {
                Password::sendResetLink($request->only('email'));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('status', 'Если аккаунт с этим email существует, мы отправили ссылку для восстановления пароля.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('store.reset-password', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $this->normalize($request);
        $request->validate(['token' => 'required|string', 'email' => 'required|email|max:254', 'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()]]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token') + ['is_admin' => false], function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => 'Ссылка недействительна или устарела. Запросите новую.']);
        }

        return redirect()->route('login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
    }

    private function normalize(Request $request): void
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
    }
}
