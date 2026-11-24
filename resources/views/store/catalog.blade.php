@extends('store.layout')
@section('title', ($category?->name ?? 'Каталог').' — NEXORA')
@section('content')
<section class="container catalog-page">
<nav class="breadcrumbs" aria-label="Путь по каталогу"><a href="{{ route('store') }}">Главная</a><span>/</span><a href="{{ route('catalog') }}">Каталог</a>@foreach($breadcrumbs as $crumb)<span>/</span>@if($loop->last)<span aria-current="page">{{ $crumb->name }}</span>@else<a href="{{ route('category.show', $crumb) }}">{{ $crumb->name }}</a>@endif @endforeach</nav>
<div class="eyebrow">ТВОИ НОВЫЕ ВОЗМОЖНОСТИ</div><h1>{{ $category?->name ?? 'Каталог программ' }}<span class="accent">.</span></h1>
<p class="muted catalog-intro">{{ $category?->description ?: 'Выбери раздел, найди нужную программу и получи свой цифровой доступ.' }}</p>
<div class="catalog-tools"><div class="filter-group" role="group" aria-label="Фильтр наличия"><button class="filter active" type="button" data-filter="all" aria-pressed="true">Все</button><button class="filter" type="button" data-filter="available" aria-pressed="false">В наличии</button></div>
<label class="search-box"><input id="catalog-search" type="search" placeholder="Найти раздел или программу" aria-label="Найти раздел или программу"></label>
<label class="catalog-sort"><span class="sr-only">Сортировка</span><select data-catalog-sort aria-label="Сортировка"><option value="default">По порядку</option><option value="name">По названию</option><option value="price">Сначала дешевле</option></select></label></div>
@include('store.categories')
@if($products->isNotEmpty())<h2 class="catalog-products-title">{{ $category ? 'Товары раздела' : 'Все программы' }}</h2>@include('store.products')@endif
@if($categories->isEmpty() && $products->isEmpty())<div class="empty-catalog"><div class="empty-icon" aria-hidden="true">⊞</div><div><h3>Здесь скоро появятся товары</h3><p>Мы наполняем этот раздел. Загляните немного позже.</p></div></div>@endif
<p class="no-results" id="no-results" hidden>По вашему запросу ничего не найдено. Попробуйте другое название или измените фильтр.</p>
<p class="payment-note">ⓘ Онлайн-оплата пока не подключена. Оплату заказа подтверждает администратор.</p>
</section>
@endsection
