<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateStoreAdmin extends Command
{
    protected $signature = 'store:create-admin {username}';

    protected $description = 'Создать администратора магазина (пароль вводится скрыто)';

    public function handle(): int
    {
        $username = $this->argument('username');
        if (User::where('username', $username)->exists()) {
            $this->error('Пользователь уже существует.');

            return self::FAILURE;
        }
        $password = $this->secret('Пароль (не менее 12 символов)');
        if (strlen($password ?? '') < 12) {
            $this->error('Пароль слишком короткий.');

            return self::FAILURE;
        }
        $user = new User;
        $user->forceFill(['name' => $username, 'username' => $username, 'email' => $username.'@localhost.invalid', 'password' => $password, 'is_admin' => true])->save();
        $this->info('Администратор создан.');

        return self::SUCCESS;
    }
}
