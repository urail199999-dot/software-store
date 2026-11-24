@extends('store.layout')
@section('title', 'Подтверждение email — NEXORA')
@section('content')
<section class="container auth-section verification-section"><div class="card auth-card"><div class="eyebrow">ЕЩЁ ОДИН ШАГ</div><h1>Подтвердите<br>свою почту<span class="accent">.</span></h1><p class="muted">Откройте письмо, отправленное на <strong>{{ auth()->user()->email }}</strong>, и нажмите «Подтвердить email».</p>
@if(session('status') === 'verification-link-sent')<div class="notice" role="status">Письмо отправлено. Если его нет во входящих, проверьте папку «Спам».</div>@endif
@if(session('mail-error'))<div class="error" role="alert">{{ session('mail-error') }}</div>@endif
<p class="form-note">Ссылка действует 60 минут. Подтверждение открывает доступ к личному кабинету и покупкам.</p>
<form method="post" action="{{ route('verification.send') }}">@csrf<button class="button full" type="submit">Отправить письмо повторно</button></form>
<form method="post" action="{{ route('logout') }}" class="auth-logout">@csrf<button class="text-link" type="submit">Выйти из аккаунта</button></form>
</div></section>
@endsection
