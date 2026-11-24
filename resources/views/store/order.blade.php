@extends('store.layout')
@section('title', 'Заказ №'.$order->id)
@section('content')
<h1>Заказ №{{ $order->id }}</h1><div class="card">
<h2>{{ $order->product_name }}</h2><p>{{ $order->email }}</p><div class="price">{{ number_format($order->amount / 100, 2, ',', ' ') }} ₽</div>
@if($order->status === 'paid' && $order->licenseKey)
<p class="notice">Оплата подтверждена. Ваша программа готова к скачиванию.</p>
<h2>Ключ активации</h2><code>{{ $order->licenseKey->key }}</code><a class="button" href="{{ route('order.download', $token) }}" rel="noreferrer">Скачать программу</a>
@else
<p class="notice">Заказ ожидает оплаты. Онлайн-оплата ещё не настроена. После получения денег администратор подтвердит заказ, и здесь появятся ключ и ссылка.</p>
<a class="button" href="{{ route('order.show', $token) }}">Проверить статус</a>
@endif
<p class="muted">Сохраните адрес этой страницы: это ваш доступ к заказу. Не передавайте его другим людям. Отправка заказа на email пока не подключена.</p>
</div>
@endsection
