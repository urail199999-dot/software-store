@extends('store.layout')
@section('content')
<section class="hero container">
<div class="hero-copy">
<div class="eyebrow"><span class="line"></span> ТВОЯ ТОЧКА ДОСТУПА</div>
<h1>Твой софт.<br>Твой следующий<br><span>уровень.</span></h1>
<p class="hero-description">Больше возможностей для твоих задач.<br>Программы и ключи активации — в одном месте.</p>
<div class="hero-actions"><a class="button" href="#catalog">Открыть каталог <span class="button-icon" aria-hidden="true">⊞</span></a><a class="text-link" href="#how-it-works">Как это работает</a></div>
<div class="hero-caption"><span class="mini-lock" aria-hidden="true">▣</span> Персональный ключ к каждой покупке</div>
</div>
<div class="hero-art" aria-hidden="true">
<div class="art-grid"></div><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="orbit-point"></div>
<div class="art-coordinate coordinate-top">NX / ACCESS SYSTEM</div><div class="art-coordinate coordinate-bottom">YOUR NEXT LEVEL IS UNLOCKED</div>
<div class="access-card"><div class="access-top"><span class="access-logo">N<span>·</span></span><span>DIGITAL<br>ACCESS</span></div>
<div class="key-art"><div class="key-ring"></div><div class="key-stem"></div><div class="key-tooth tooth-one"></div><div class="key-tooth tooth-two"></div></div>
<div class="card-line"></div><div class="access-bottom"><span>ONE KEY.<br>NEW POSSIBILITIES.</span><span class="barcode">▎▍▏▌▎▍▏▌▎▏</span></div></div>
<div class="floating-tag"><span class="tag-icon">⌁</span><div>Твой доступ<small>Под твоим контролем</small></div><span class="tag-check">✓</span></div>
<span class="art-plus plus-one">+</span><span class="art-plus plus-two">+</span>
</div>
</section>
<div class="benefit-strip"><div class="container benefits"><div><span>⌘</span><p>Индивидуальные<small>ключи активации</small></p></div><div><span>▤</span><p>Прозрачная покупка<small>цена до оформления</small></p></div><div><span>↗</span><p>Прямой доступ<small>к загрузке программы</small></p></div></div></div>
<section id="catalog" class="container catalog-section">
<div class="section-heading"><div><div class="eyebrow">ВЫБИРАЙ СВОИ ВОЗМОЖНОСТИ</div><h2>Каталог программ<span class="accent">.</span></h2></div><span class="catalog-count">{{ $products->count() }} {{ $products->count() === 1 ? 'продукт' : 'продуктов' }}</span></div>
<div class="catalog-tools"><div class="filter-group" role="group" aria-label="Фильтр наличия"><button class="filter active" type="button" data-filter="all" aria-pressed="true">Все программы</button><button class="filter" type="button" data-filter="available" aria-pressed="false">В наличии</button></div><label class="search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg><input id="catalog-search" type="search" placeholder="Найти программу" aria-label="Найти программу"></label></div>
@include('store.categories')
@include('store.products')
@if($categories->isEmpty() && $products->isEmpty())<div class="empty-catalog"><div class="empty-icon" aria-hidden="true">⊞</div><div><h3>Новые возможности уже на подходе</h3><p>Мы наполняем каталог программ. Загляните немного позже.</p></div></div>@endif
<p class="no-results" id="no-results" hidden>Ничего не найдено. Попробуйте другое название.</p>
<p class="payment-note"><span aria-hidden="true">ⓘ</span> Онлайн-оплата пока не подключена. Оплату заказа подтверждает администратор.</p>
</section>
<section class="how-section" id="how-it-works"><div class="container"><div class="section-heading"><div><div class="eyebrow">ОТ ВЫБОРА ДО ЗАПУСКА</div><h2>Всё начинается с ключа<span class="accent">.</span></h2></div><p class="section-description">Понятный путь к твоей программе.<br>Без лишних шагов.</p></div>
<div class="steps"><article><span class="step-label">ШАГ 01 <span>⊞</span></span><h3>Найди своё</h3><p>Выбери программу под свои задачи.<br>Описание и цена — в карточке товара.</p></article><article><span class="step-label">ШАГ 02 <span>▤</span></span><h3>Оформи заказ</h3><p>Укажи email и сохрани страницу заказа. Дождись подтверждения оплаты.</p></article><article><span class="step-label">ШАГ 03 <span>⌘</span></span><h3>Открой возможности</h3><p>Получи ключ на странице заказа, скачай программу и активируй её.</p></article></div>
</div></section>
<section id="delivery" class="container delivery-section"><div class="delivery-panel"><div class="delivery-symbol" aria-hidden="true">[<span>⌘</span>]</div><div><div class="eyebrow">ВСЁ ДЛЯ СТАРТА</div><h2>Одна покупка. <br>Твой личный доступ.</h2><p>Ссылка на программу и индивидуальный ключ появятся<br>на странице заказа после подтверждения оплаты.</p><a class="text-link" href="#catalog">Выбрать программу</a></div><div class="delivery-meta"><span>01 / ВЫБОР</span><span>02 / ПОКУПКА</span><strong>03 / ДОСТУП</strong></div></div></section>
<section id="faq" class="container faq-section"><div><div class="eyebrow">ЕСТЬ ВОПРОС?</div><h2>Разберёмся <br>с деталями<span class="accent">.</span></h2><p class="muted">Самое важное о покупке<br>и получении программы.</p></div><div class="faq-list">
<details><summary>Где я получу программу и ключ?<span>+</span></summary><p>После подтверждения оплаты на странице заказа откроются ключ активации и кнопка скачивания программы.</p></details>
<details><summary>Нужно ли создавать аккаунт?<span>+</span></summary><p>Можно зарегистрироваться по email и подтвердить почту. Заказы, оформленные после входа, будут доступны в личном кабинете. Гостевая покупка тоже доступна: сохраните секретную ссылку на заказ.</p></details>
<details><summary>Как сейчас проходит оплата?<span>+</span></summary><p>Онлайн-оплата ещё не подключена. После фактического получения денег администратор подтверждает заказ. До этого момента ключ и ссылка остаются закрыты.</p></details>
<details><summary>Придёт ли ключ на почту?<span>+</span></summary><p>Отправка писем пока не подключена. Все данные покупки доступны на странице заказа. Сохраните её адрес и не передавайте другим людям.</p></details>
</div></section>
@endsection
