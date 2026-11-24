@extends('store.layout')
@section('title', 'Новый пароль — NEXORA')
@section('content')
<section class="container auth-section verification-section"><div class="card auth-card"><div class="eyebrow">ВОССТАНОВЛЕНИЕ ДОСТУПА</div><h2>Новый пароль</h2>
<form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
<label for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email', $email) }}" required maxlength="254" autocomplete="email">
<label for="new-password">Новый пароль</label><input id="new-password" type="password" name="password" required minlength="12" maxlength="4096" autocomplete="new-password"><p class="form-note">Не менее 12 символов, заглавные и строчные латинские буквы, цифры.</p>
<label for="new-password-confirm">Повторите пароль</label><input id="new-password-confirm" type="password" name="password_confirmation" required minlength="12" maxlength="4096" autocomplete="new-password"><button class="button full" type="submit">Сохранить пароль</button></form>
</div></section>
@endsection
