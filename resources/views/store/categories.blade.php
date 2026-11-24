@if($categories->isNotEmpty())
<div class="category-grid" data-sort-grid>
@foreach($categories as $item)
<a class="category-card" href="{{ route('category.show', $item) }}" data-category data-name="{{ $item->name }}" data-available="{{ $item->catalog_available ? 'yes' : 'no' }}" data-price="{{ $item->catalog_price ?? 999999999 }}">
@if($item->image)<img src="{{ Storage::disk('public')->url($item->image) }}" alt="" loading="lazy" width="600" height="400">@else<div class="category-placeholder" aria-hidden="true">{{ mb_substr($item->name, 0, 1) }}</div>@endif
<div class="category-shade"></div><div class="category-card-content"><span class="category-label">{{ $category ? 'ПОДРАЗДЕЛ' : 'РАЗДЕЛ' }}</span><h3>{{ $item->name }}</h3><div class="category-meta"><span>{{ $item->catalog_count }} товаров</span><span>{{ $item->catalog_price !== null ? 'от '.number_format($item->catalog_price / 100, 0, ',', ' ').' ₽' : 'Скоро в продаже' }}</span></div></div>
</a>
@endforeach
</div>
@endif
