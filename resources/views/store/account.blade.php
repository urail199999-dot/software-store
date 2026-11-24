@extends('store.layout')
@section('title', 'Личный кабинет — NEXORA')
@section('content')
<section class="container account-section"><div class="section-heading"><div><div class="eyebrow">ТВОЙ ЦИФРОВОЙ ДОСТУП</div><h1>Привет, {{ auth()->user()->name }}<span class="accent">.</span></h1><p class="muted account-email">{{ auth()->user()->email }} <span class="verified-badge">Email подтверждён</span></p></div><form method="post" action="{{ route('logout') }}">@csrf<button class="filter" type="submit">Выйти</button></form></div>
<h2 class="catalog-products-title">Мои заказы</h2>
@if($orders->isEmpty())<div class="empty-catalog"><div class="empty-icon" aria-hidden="true">⊞</div><div><h3>Твои возможности впереди</h3><p>Заказы, оформленные после входа в аккаунт, появятся здесь.</p><a class="button" href="{{ route('catalog') }}">Перейти в каталог</a></div></div>
@else<div class="account-orders">@foreach($orders as $order)<a class="account-order" href="{{ route('account.order', $order) }}"><div><span class="eyebrow">ЗАКАЗ №{{ $order->id }} · {{ $order->created_at->format('d.m.Y') }}</span><h3>{{ $order->product_name }}</h3></div><span class="order-badge {{ $order->status === 'paid' ? 'paid' : '' }}">{{ $order->status === 'paid' ? 'Оплачен · Ключ доступен' : 'Ожидает оплаты' }}</span><strong>{{ number_format($order->amount / 100, 2, ',', ' ') }} ₽</strong></a>@endforeach</div><div class="account-pagination">@if($orders->previousPageUrl())<a class="text-link" href="{{ $orders->previousPageUrl() }}">Предыдущая страница</a>@endif<span>{{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>@if($orders->nextPageUrl())<a class="text-link" href="{{ $orders->nextPageUrl() }}">Следующая страница</a>@endif</div>@endif
</section>
@endsection
