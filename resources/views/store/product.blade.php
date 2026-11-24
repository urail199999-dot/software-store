@extends('store.layout')
@section('title', $product->name.' — NEXORA')
@section('content')
<section class="container catalog-page">
<nav class="breadcrumbs" aria-label="Путь по каталогу"><a href="{{ route('store') }}">Главная</a><span>/</span><a href="{{ route('catalog') }}">Каталог</a>@if($product->category)<span>/</span><a href="{{ route('category.show', $product->category) }}">{{ $product->category->name }}</a>@endif<span>/</span><span aria-current="page">{{ $product->name }}</span></nav>
<div class="product-detail-layout"><div>
@if($product->image)<img class="product-main-image" id="product-main-image" src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" width="1200" height="800">@elseif($product->gallery)<img class="product-main-image" id="product-main-image" src="{{ Storage::disk('public')->url($product->gallery[0]) }}" alt="{{ $product->name }}" width="1200" height="800">@else<div class="product-image-placeholder" aria-hidden="true">[{{ mb_substr($product->name, 0, 1) }}]</div>@endif
@if($product->gallery)<div class="product-thumbnails" role="group" aria-label="Изображения товара">@foreach(collect([$product->image])->merge($product->gallery)->filter()->unique() as $image)<button type="button" data-gallery-image="{{ Storage::disk('public')->url($image) }}" aria-label="Показать изображение {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><img src="{{ Storage::disk('public')->url($image) }}" alt="" width="140" height="100" loading="lazy"></button>@endforeach</div>@endif
</div><div class="product-detail-info"><div class="eyebrow">{{ $product->category?->name ?? 'ЦИФРОВАЯ ПРОГРАММА' }}</div><h1>{{ $product->name }}</h1><span class="order-badge {{ $product->available_keys_count > 0 ? 'paid' : '' }}">{{ $product->available_keys_count > 0 ? 'В наличии' : 'Нет в наличии' }}</span><div class="price">{{ number_format($product->price / 100, 2, ',', ' ') }} ₽</div>
@if($product->available_keys_count > 0)<form action="{{ route('checkout', $product) }}" method="post">@csrf<label for="buyer-email">Ваш email</label><input id="buyer-email" type="email" name="email" @readonly(auth()->check()) required maxlength="254" value="{{ auth()->user()?->email ?? old('email') }}" placeholder="you@example.com" autocomplete="email"><button class="button full" type="submit">Оформить заказ</button></form>@else<button class="sold-out" disabled>Ожидаем поступление</button>@endif
<p class="form-note">Ключ и ссылка откроются на странице заказа после подтверждения оплаты администратором. Онлайн-оплата пока не подключена.</p></div></div>
<div class="product-description-panel"><h2>О программе</h2><p class="description">{{ $product->description }}</p></div>
</section>
@endsection
