@if($products->isNotEmpty())
<div class="product-grid" data-sort-grid>@foreach($products as $product)
<article class="product-card" data-product data-name="{{ $product->name }}" data-available="{{ $product->available_keys_count > 0 ? 'yes' : 'no' }}" data-price="{{ $product->price }}">
<a class="product-cover" href="{{ route('product.show', $product) }}" aria-label="{{ $product->name }}">
@if($product->image)<img class="product-cover-image" src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" loading="lazy" width="600" height="400">@else<span class="software-glyph" aria-hidden="true">[<b>{{ mb_substr($product->name, 0, 1) }}</b>]</span>@endif
<span class="product-type">{{ $product->category?->name ?? 'DIGITAL SOFTWARE' }}</span><span class="stock {{ $product->available_keys_count > 0 ? '' : 'unavailable' }}">{{ $product->available_keys_count > 0 ? 'В наличии' : 'Нет в наличии' }}</span></a>
<div class="product-body"><h3><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h3><p class="description product-excerpt">{{ Str::limit($product->description, 160) }}</p><div class="product-price"><span>{{ number_format($product->price / 100, 2, ',', ' ') }} <small>₽</small></span><small>Цифровой ключ</small></div>
<a class="button full product-details-link" href="{{ route('product.show', $product) }}">Подробнее о товаре</a>
@if($product->available_keys_count > 0)<details class="purchase"><summary>Оформить заказ <span aria-hidden="true">+</span></summary><form action="{{ route('checkout', $product) }}" method="post">@csrf<label for="email-{{ $product->id }}">Ваш email</label><input id="email-{{ $product->id }}" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required maxlength="254" autocomplete="email"><button class="button full" type="submit">Перейти к заказу</button><p class="form-note">Ключ откроется после подтверждения оплаты.</p></form></details>
@else<button class="sold-out" disabled>Ожидаем поступление</button>@endif</div></article>
@endforeach</div>
@endif
