<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="referrer" content="no-referrer">
<meta name="theme-color" content="#101216"><meta name="description" content="NEXORA — магазин программ и цифровых ключей. Выберите программу и получите доступ на странице заказа.">
<title>@yield('title', 'NEXORA — программы и цифровые ключи')</title>
<link rel="stylesheet" href="{{ asset('store.css') }}?v=5">
<script src="{{ asset('store.js') }}?v=4" defer></script>
</head>
<body>
<canvas id="code-background" aria-hidden="true"></canvas>
<button class="code-motion-toggle" type="button" data-code-motion aria-pressed="false" hidden>Пауза анимации</button>
<a class="skip" href="#main">К содержимому</a>
<header class="site-header"><div class="container header-inner">
<a class="brand" href="{{ route('store') }}" aria-label="NEXORA — на главную"><span class="brand-symbol" aria-hidden="true">N<span>·</span></span><span>NEXORA<small>DIGITAL STORE</small></span></a>
<nav aria-label="Основная навигация"><a href="{{ route('catalog') }}">Каталог</a><a href="{{ route('store') }}#how-it-works">Как купить</a><a href="{{ route('store') }}#faq">Вопросы</a></nav>
@auth
<a class="header-link account-header-link" href="{{ auth()->user()->is_admin ? '/admin' : route('account') }}">{{ auth()->user()->is_admin ? 'Админка' : 'Личный кабинет' }} <span class="tiny-key" aria-hidden="true">⌘</span></a>
@else
<div class="header-auth"><a href="{{ route('login') }}">Войти</a><a class="auth-signup-link" href="{{ route('register') }}">Регистрация</a></div>
@endauth
</div></header>
<main id="main">@if($errors->any())<div class="container"><div class="error" role="alert">{{ $errors->first() }}</div></div>@endif
@if(session('status') && session('status') !== 'verification-link-sent')<div class="container"><div class="notice auth-status" role="status">{{ session('status') }}</div></div>@endif
@yield('content')</main>
<footer class="site-footer"><div class="container">
<div class="footer-top"><a class="brand" href="{{ route('store') }}"><span class="brand-symbol" aria-hidden="true">N<span>·</span></span><span>NEXORA<small>DIGITAL STORE</small></span></a><p>Технологии под твои задачи.<br>Доступ — в твоих руках.</p><div><a href="{{ route('catalog') }}">Каталог программ</a><a href="{{ route('store') }}#faq">Вопросы и ответы</a></div></div>
<div class="footer-bottom"><span>© {{ date('Y') }} NEXORA</span><span>Программы · Лицензии · Цифровой доступ</span><a href="/admin">Для администратора</a></div>
</div></footer>
</body></html>
