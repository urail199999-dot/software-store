@extends('store.layout')
@section('content')
<h1>Каталог программ</h1><p class="muted">Выберите программу. После подтверждения оплаты в заказе появятся ссылка для скачивания и ключ активации.</p>
<div class="notice">Приём онлайн-платежей пока не подключён. Заказы подтверждает администратор после получения оплаты.</div>
<div class="grid">@forelse($products as $product)<article class="card">
<h2>{{ $product->name }}</h2><p class="description">{{ $product->description }}</p>
<div class="price">{{ number_format($product->price / 100, 2, ',', ' ') }} ₽</div>
@if($product->available_keys_count > 0)
<form action="{{ route('checkout', $product) }}" method="post">@csrf
<label for="email-{{ $product->id }}">Ваш email</label><input id="email-{{ $product->id }}" type="email" name="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email">
<button type="submit">Оформить заказ</button></form>
@else<p class="muted">Временно нет в наличии</p>@endif
</article>@empty<div class="card"><h2>Каталог готов к наполнению</h2><p class="muted">Добавьте программу и ключи активации в панели администратора, затем опубликуйте товар.</p></div>@endforelse</div>
@endsection
