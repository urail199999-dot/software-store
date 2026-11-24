<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(fn ($user, string $url) => (new MailMessage)
            ->subject('Подтвердите email — NEXORA')->greeting('Здравствуйте, '.$user->name.'!')
            ->line('Подтвердите адрес электронной почты, чтобы открыть личный кабинет NEXORA.')
            ->action('Подтвердить email', $url)->line('Ссылка действует 60 минут.')
            ->line('Если вы не регистрировались, просто проигнорируйте это письмо.')->salutation('Команда NEXORA'));
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Восстановление пароля — NEXORA')->greeting('Здравствуйте!')
            ->line('Вы получили это письмо, потому что был запрошен сброс пароля вашего аккаунта.')
            ->action('Установить новый пароль', route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->line('Ссылка действует 60 минут. Если вы не запрашивали сброс, никаких действий не требуется.')->salutation('Команда NEXORA'));
        if ($this->app->runningInConsole() && PHP_OS_FAMILY === 'Windows') {
            // PHP needs these directories when Artisan starts its HTTP server.
            ServeCommand::$passthroughVariables = array_values(array_unique([
                ...ServeCommand::$passthroughVariables,
                'TEMP',
                'TMP',
                'TMPDIR',
            ]));
        }
    }
}
