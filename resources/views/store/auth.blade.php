@extends('store.layout')
@section('title', ($mode === 'register' ? 'Регистрация' : ($mode === 'forgot' ? 'Восстановление пароля' : 'Вход')).' — NEXORA')
@section('content')
<section class="container auth-section"><div class="auth-story"><div class="eyebrow">ТВОЙ ЛИЧНЫЙ ДОСТУП</div><h1>Все покупки.<br>В одном месте<span class="accent">.</span></h1><p class="muted">Программы, ключи активации и история заказов — в твоём личном кабинете.</p><div class="auth-story-symbol" aria-hidden="true">[⌘]</div></div>
<div class="card auth-card"><div class="auth-tabs"><a class="{{ $mode === 'login' ? 'active' : '' }}" href="{{ route('login') }}">Вход</a><a class="{{ $mode === 'register' ? 'active' : '' }}" href="{{ route('register') }}">Регистрация</a></div>
<h2>{{ $mode === 'register' ? 'Создать аккаунт' : ($mode === 'forgot' ? 'Забыли пароль?' : 'С возвращением') }}</h2>
<p class="muted auth-description">{{ $mode === 'register' ? 'Зарегистрируйтесь и подтвердите свою почту.' : ($mode === 'forgot' ? 'Отправим на email ссылку для установки нового пароля.' : 'Войдите по email и паролю.') }}</p>
@if($mode === 'register' && !$mailReady)<div class="notice auth-unavailable">Регистрация временно недоступна. Попробуйте немного позже.</div>@endif
<form method="post" action="{{ $mode === 'register' ? route('register') : ($mode === 'forgot' ? route('password.email') : route('login')) }}">@csrf
@if($mode === 'register')<label for="customer-name">Как к вам обращаться</label><input id="customer-name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" placeholder="Ваше имя">@endif
<label for="customer-email">Email</label><input id="customer-email" type="email" name="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email" placeholder="you@example.com">
@if($mode !== 'forgot')
<label for="customer-password">Пароль</label><input id="customer-password" type="password" name="password" required {{ $mode === 'register' ? 'minlength=12' : '' }} maxlength="4096" autocomplete="{{ $mode === 'register' ? 'new-password' : 'current-password' }}">
@if($mode === 'register')<p class="form-note">Не менее 12 символов: заглавные и строчные латинские буквы, цифры.</p><label for="customer-password-confirmation">Повторите пароль</label><input id="customer-password-confirmation" type="password" name="password_confirmation" required minlength="12" maxlength="4096" autocomplete="new-password">
@else<div class="auth-options"><label class="remember-label"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Запомнить меня</label><a href="{{ route('password.request') }}">Забыли пароль?</a></div>@endif
@endif
@if($captchaReady || $mailReady || $mode !== 'register')
@include('store.recaptcha')
@endif
<button class="button full" type="submit" @disabled(!$captchaReady || ($mode === 'register' && !$mailReady))>{{ $mode === 'register' ? 'Зарегистрироваться' : ($mode === 'forgot' ? 'Отправить ссылку' : 'Войти') }}</button>
</form>
@if($mode === 'register')<p class="form-note">После регистрации отправим письмо со ссылкой для подтверждения адреса. До подтверждения доступ к покупкам в кабинете закрыт.</p>@endif
</div></section>
@endsection
