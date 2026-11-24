@extends('store.layout')
@section('title', 'Заказ №'.$order->id.' — NEXORA')
@section('content')
<section class="container order-section"><a class="text-link" href="{{ route('store') }}#catalog">Каталог программ</a><div class="eyebrow order-eyebrow">ТВОЙ ЦИФРОВОЙ ДОСТУП</div><h1>Заказ №{{ $order->id }}<span class="accent">.</span></h1>
<div class="order-layout"><div class="card order-card"><div class="order-card-heading"><span class="eyebrow">ТВОЯ ПРОГРАММА</span><span class="order-badge {{ $order->status === 'paid' ? 'paid' : '' }}">{{ $order->status === 'paid' ? 'Оплачен' : 'Ожидает оплаты' }}</span></div><h2>{{ $order->product_name }}</h2><p class="muted">{{ $order->email }}</p><div class="price">{{ number_format($order->amount / 100, 2, ',', ' ') }} ₽</div>
@if($order->status === 'paid' && $order->licenseKey)
<div class="notice">Оплата подтверждена. Ваша программа готова к скачиванию.</div>
<label for="activation-key">Ключ активации</label><code id="activation-key">{{ $order->licenseKey->key }}</code><button class="copy-key text-link" type="button" data-copy-key>Скопировать ключ</button><span id="copy-status" class="muted" role="status"></span><a class="button full" href="{{ route('order.download', $token) }}" rel="noreferrer">Скачать программу</a>
@else
<div class="notice">После подтверждения оплаты здесь появятся ключ активации и ссылка на программу.</div><p class="muted">Онлайн-оплата ещё не настроена. Заказ подтвердит администратор после фактического получения денег.</p>
<a class="button" href="{{ route('order.show', $token) }}">Проверить статус</a>
@endif</div><aside class="order-aside"><span class="aside-symbol" aria-hidden="true">⌘</span><h3>Сохрани свой доступ</h3><p>Адрес этой страницы — ваш доступ к заказу. Добавьте его в закладки и не передавайте другим людям.</p><div class="aside-divider"></div><p>Отправка заказа на email пока не подключена. Ключ и ссылка будут доступны здесь.</p></aside></div></section>
@endsection
