@php
    $soldCounts = $soldCounts ?? collect();
    $menuImages = [
        'chicken-burger' => 'chicken-burger-pink-v8.png',
        'egg-bunwich' => 'egg-bunwich-pink-v8.png',
        'cheesy-chicken' => 'cheesy-chicken-pink-v8.png',
        'cheesy-chicken-hotdog-sandwich' => 'cheesy-chicken-pink-v8.png',
        'cheesy-chicken-hotdog' => 'cheesy-chicken-pink-v8.png',
        'fries' => 'fries-pink-v8.png',
        'classic-fries' => 'fries-pink-v8.png',
        'ice-cream' => 'ice-cream-pink-v8.png',
        'mi-cusina-ice-cream' => 'ice-cream-pink-v8.png',
        'creamy-carbonara' => 'creamy-carbonara-pink-v8.png',
        'classic-spaghetti' => 'classic-spaghetti-pink-v8.png',
        'chicken-nugget' => 'chicken-nugget-pink-v8.png',
        'chicken-nuggets' => 'chicken-nugget-pink-v8.png',
        'chicken-nuggets-rice-bowl' => 'chicken-nugget-pink-v8.png',
        '2-pcs-chicken-meal' => '2-pcs-chicken-meal-pink-v8.png',
        '2-pc-chicken-meal' => '2-pcs-chicken-meal-pink-v8.png',
        '1-pc-chicken-meal' => '1-pc-chicken-meal-pink-v8.png',
        'chicken-spaghetti' => 'chicken-spaghetti-pink-v8.png',
        'chicken-adobo-bunwich' => 'chicken-adobo-bunwich-pink-v8.png',
        'chicken-fillet' => 'chicken-fillet-pink-v8.png',
        'chicken-burger-spaghetti' => 'chicken-burger-spaghetti-pink-v8.png',
        'chicken-adobo-flakes' => 'chicken-adobo-flakes-pink-v8.png',
        'chicken-teriyaki' => 'chicken-teriyaki-pink-v8.png',
        'ham-bowl' => 'ham-bowl-pink-v8.png',
        'siomai' => 'siomai-pink-v8.png',
        'siomai-egg-rice-bowl' => 'siomai-egg-pink-v8.png',
    ];

    $normalizeMenuSlug = function ($title) {
        return \Illuminate\Support\Str::of($title)
            ->slug()
            ->replace('chessy', 'cheesy')
            ->replace('bundwich', 'bunwich')
            ->replace('tereyaki', 'teriyaki')
            ->toString();
    };

    $getMenuCategory = function ($food) {
        $text = \Illuminate\Support\Str::of(($food->title ?? '') . ' ' . ($food->detail ?? '') . ' ' . ($food->details ?? ''))
            ->lower()
            ->toString();

        if (str_contains($text, 'ice cream')) {
            return 'Desserts';
        }

        if (str_contains($text, 'fries') || str_contains($text, 'siomai')) {
            return 'Sides & Snacks';
        }

        if (str_contains($text, 'spaghetti') || str_contains($text, 'carbonara')) {
            return 'Pasta';
        }

        if (str_contains($text, 'bowl') || str_contains($text, 'rice')) {
            return 'Rice Bowls';
        }

        if (str_contains($text, 'burger') || str_contains($text, 'bunwich') || str_contains($text, 'sandwich') || str_contains($text, 'hotdog')) {
            return 'Burgers & Bunwiches';
        }

        if (str_contains($text, 'chicken') || str_contains($text, 'nugget') || str_contains($text, 'fillet')) {
            return 'Chicken Meals';
        }

        return 'Other';
    };

    $menuCategories = collect($data)
        ->map(fn ($food) => $getMenuCategory($food))
        ->unique()
        ->sort()
        ->values();
@endphp

<div id="blog" class="mic-marketplace">
    <div class="mic-marketplace-inner">
        <section class="mic-menu-hero">
            <div class="mic-menu-hero-copy">
                <span class="mic-eyebrow">FRESHLY PREPARED DAILY</span>
                <h2>Good food,<br>made for you.</h2>
                <p>Explore Mi Cusina favorites—from filling rice bowls to comforting pasta and snacks.</p>
                <a href="#micProductGrid" class="mic-hero-cta">Explore menu <i class="fa fa-arrow-right"></i></a>
            </div>
            <div class="mic-menu-hero-dish"><img src="{{ asset('assets/imgs/chicken-adobo-flakes.png') }}" alt="Mi Cusina featured meal"></div>
            <span class="mic-menu-count">{{ count($data) }} menu items</span>
        </section>
        <div class="mic-menu-heading">
            <div><span class="mic-eyebrow">OUR MENU</span><h2>Choose your favorite</h2></div>
        </div>
        <div class="mic-product-grid" id="micProductGrid">
            @foreach($data as $food)
                @php
                    $sold = (int) ($soldCounts[$food->title] ?? 0);
                    $price = (float) $food->price;
                    $menuImage = $menuImages[$normalizeMenuSlug($food->title)] ?? null;
                    $foodImagePath = $menuImage ? 'assets/imgs/' . $menuImage : 'food_img/' . $food->image;
                    $fallbackFoodImagePath = 'food_img/' . $food->image;
                    $foodImage = asset($foodImagePath) . '?v=' . (file_exists(public_path($foodImagePath)) ? filemtime(public_path($foodImagePath)) : time());
                    $fallbackFoodImage = asset($fallbackFoodImagePath) . '?v=' . (file_exists(public_path($fallbackFoodImagePath)) ? filemtime(public_path($fallbackFoodImagePath)) : time());
                @endphp

                <article class="mic-product-card"
                    data-title="{{ e($food->title) }}"
                    data-menu-slug="{{ $normalizeMenuSlug($food->title) }}"
                    data-detail="{{ e($food->detail) }}"
                    data-price="{{ $price }}"
                    data-sold="{{ $sold }}"
                    data-stock="{{ $food->stock }}"
                    data-date="{{ optional($food->created_at)->timestamp ?? 0 }}"
                    data-category="{{ e($getMenuCategory($food)) }}"
                    data-image="{{ $foodImage }}"
                    data-action="{{ url('/add_cart', $food->id) }}">
                    <button class="mic-product-open" type="button" aria-label="View {{ $food->title }}">
                        <span class="mic-product-image-wrap">
                            <img src="{{ $foodImage }}" data-fallback-image="{{ $fallbackFoodImage }}" alt="{{ $food->title }}" onerror="if (this.src !== this.dataset.fallbackImage) { this.src = this.dataset.fallbackImage; }">
                            <span class="mic-product-ribbon">Mi Cusina</span>
                        </span>
                        <span class="mic-product-body">
                            <span class="mic-product-title">{{ $food->title }}</span>
                            <span class="mic-product-price">&#8369;{{ number_format($price, 2) }}</span>
                            <span class="mic-product-description">{{ \Illuminate\Support\Str::limit($food->detail, 96) }}</span>
                            <span class="mic-stock {{ $food->stock <= 0 ? 'is-out' : ($food->stock < 5 ? 'is-low' : 'is-available') }}">
                                {{ $food->stock > 0 ? $food->stock . ' available' : 'Out of stock' }}
                            </span>
                            <span class="mic-product-meta"><span>{{ $sold }} sold</span></span>
                            <span class="mic-location">Mi Cusina Kitchen</span>
                        </span>
                    </button>

                    <form action="{{ url('/add_cart', $food->id) }}" method="post" class="mic-card-cart">
                        @csrf
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" {{ $food->stock <= 0 ? 'disabled' : '' }}>{{ $food->stock > 0 ? 'Add to Cart' : 'Out of Stock' }}</button>
                        <button type="submit" name="buy_now" value="1" {{ $food->stock <= 0 ? 'disabled' : '' }}>{{ $food->stock > 0 ? 'Buy Now' : 'Out of Stock' }}</button>
                    </form>
                </article>
            @endforeach
        </div>
        <div class="mic-empty-category" id="micEmptyCategory">No food items found in this category.</div>
    </div>
</div>

<div class="mic-product-modal" id="micProductModal" aria-hidden="true">
    <div class="mic-menu-modal-background" aria-hidden="true"></div>
    <div class="mic-product-modal-backdrop" data-close-product></div>
    <section class="mic-product-detail" role="dialog" aria-modal="true" aria-labelledby="micProductTitle">
        <button class="mic-product-close" type="button" data-close-product aria-label="Close product">&times;</button>
        <div class="mic-detail-media">
            <img id="micProductImage" src="" alt="">
            <div class="mic-detail-thumbs">
                <button type="button" class="is-active"><img id="micProductThumb" src="" alt=""></button>
            </div>
            <div class="mic-share-row">
                <span>Share:</span>
                <span class="mic-share-dot">f</span>
                <span class="mic-share-dot">x</span>
                <span class="mic-share-dot">p</span>
            </div>
        </div>
        <div class="mic-detail-info">
            <h3 id="micProductTitle"></h3>
            <div class="mic-detail-price" id="micProductPrice"></div>
            <div class="mic-detail-stock" id="micProductStock"></div>
            <p id="micProductDetail"></p>
            <form id="micProductForm" action="" method="post" class="mic-detail-cart">
                @csrf
                <input type="hidden" name="qty" value="1">
                <div class="mic-detail-actions">
                    <button type="submit" class="mic-outline-btn" id="micAddCartButton">Add To Cart</button>
                    <button type="submit" name="buy_now" value="1" class="mic-solid-btn" id="micBuyNowButton">Buy Now</button>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
    .mic-marketplace {
        background: #050505;
        color: #222;
        padding: 28px 0 42px;
    }

    .mic-marketplace-inner {
        margin: 0 auto;
        width: min(1240px, calc(100% - 24px));
    }

    .mic-sortbar {
        align-items: center;
        background: #fff;
        border: 1px solid #000;
        color: #000;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
        padding: 14px 22px;
    }

    .mic-sort-btn,
    .mic-category-filter summary,
    .mic-price-sort {
        background: #fff;
        border: 1px solid #000;
        color: #000;
        cursor: pointer;
        font-size: 16px;
        height: 42px;
        min-width: 112px;
    }

    .mic-category-filter {
        position: relative;
    }

    .mic-category-filter summary {
        align-items: center;
        display: inline-flex;
        gap: 10px;
        list-style: none;
        padding: 0 18px;
    }

    .mic-category-filter summary::-webkit-details-marker {
        display: none;
    }

    .mic-category-filter summary::after {
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #000;
        content: "";
        margin-left: 8px;
    }

    .mic-category-filter[open] summary {
        background: #fff;
    }

    .mic-category-filter strong {
        color: #000;
        font-size: 13px;
        font-weight: 800;
    }

    .mic-category-menu {
        background: #fff;
        border: 1px solid #000;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .16);
        display: grid;
        gap: 6px;
        left: 0;
        min-width: 230px;
        padding: 10px;
        position: absolute;
        top: calc(100% + 8px);
        z-index: 20;
    }

    .mic-category-menu button {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 5px;
        color: #000;
        cursor: pointer;
        font-size: 14px;
        padding: 10px 12px;
        text-align: left;
    }

    .mic-category-menu button:hover,
    .mic-category-menu button.is-active {
        background: #fff;
        border-color: #000;
    }

    .mic-sort-btn.is-active {
        background: #fff;
        color: #000;
    }

    .mic-price-sort {
        flex: 0 1 250px;
        padding: 0 16px;
    }

    .mic-page-count {
        color: #000;
        margin-left: auto;
    }

    .mic-page-count span {
        color: #000;
    }

    .mic-product-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .mic-product-card.is-hidden {
        display: none;
    }

    .mic-empty-category {
        border: 1px solid #2a2a2a;
        color: #d7d7d7;
        display: none;
        padding: 28px;
        text-align: center;
    }

    .mic-empty-category.is-visible {
        display: block;
    }

    .mic-product-card {
        background: #fff;
        border: 1px solid #d1d5db;
        min-width: 0;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .mic-product-card:hover {
        box-shadow: 0 6px 18px rgba(255, 255, 255, .12);
        transform: translateY(-2px);
    }

    .mic-product-open {
        background: transparent;
        border: 0;
        cursor: pointer;
        padding: 0;
        text-align: left;
        width: 100%;
    }

    .mic-product-image-wrap {
        aspect-ratio: 1 / 1;
        background: #0f0f0f;
        display: block;
        overflow: hidden;
        position: relative;
    }

    .mic-product-image-wrap img {
        display: block;
        height: 100%;
        object-fit: contain;
        width: 100%;
    }

    .mic-product-ribbon {
        background: #F88379;
        bottom: 0;
        color: #fff;
        font-size: 12px;
        left: 0;
        padding: 3px 7px;
        position: absolute;
    }

    .mic-product-body {
        display: block;
        padding: 12px 14px 10px;
    }

    .mic-product-title {
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: #000;
        display: -webkit-box;
        font-size: 17px;
        line-height: 1.24;
        min-height: 42px;
        overflow: hidden;
    }

    .mic-product-price {
        color: #000;
        display: block;
        font-size: 20px;
        margin-top: 8px;
    }

    .mic-product-meta,
    .mic-stock,
    .mic-location {
        color: #000;
        display: flex;
        font-size: 12px;
        gap: 8px;
        justify-content: space-between;
        margin-top: 8px;
    }

    .mic-stars {
        color: #000;
    }

    .mic-stock { font-weight: 700; }
    .mic-stock.is-low,
    .mic-stock.is-out { color: #dc2626; }
    .mic-stock.is-available { color: #15803d; }

    .mic-card-cart {
        display: flex;
        gap: 6px;
        padding: 0 14px 14px;
    }

    .mic-card-cart input {
        border: 1px solid #ddd;
        padding: 4px;
        width: 56px;
    }

    .mic-card-cart button {
        background: #1c1c1c;
        border: 1px solid #F88379;
        color: #F88379;
        cursor: pointer;
        flex: 1;
    }

    .mic-card-cart input:disabled,
    .mic-card-cart button:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .mic-product-modal {
        display: none;
        inset: 0;
        position: fixed;
        z-index: 4000;
    }

    .mic-product-modal.is-open {
        display: block;
    }

    .mic-product-modal-backdrop {
        background: rgba(0, 0, 0, .48);
        inset: 0;
        position: absolute;
    }

    .mic-product-detail {
        background: #fff;
        color: #222;
        display: grid;
        gap: 34px;
        grid-template-columns: minmax(300px, 470px) 1fr;
        margin: 17px auto;
        max-height: calc(100vh - 34px);
        overflow-y: auto;
        padding: 24px;
        position: relative;
        width: min(1120px, calc(100% - 28px));
    }

    .mic-product-close {
        background: transparent;
        border: 0;
        cursor: pointer;
        font-size: 32px;
        position: absolute;
        right: 14px;
        top: 8px;
    }

    .mic-detail-media > img {
        aspect-ratio: 1 / 1;
        border: 1px solid #35d7c7;
        object-fit: contain;
        width: 100%;
    }

    .mic-detail-thumbs {
        display: flex;
        gap: 10px;
        margin-top: 14px;
    }

    .mic-detail-thumbs button {
        background: #fff;
        border: 2px solid #F88379;
        height: 86px;
        padding: 0;
        width: 86px;
    }

    .mic-detail-thumbs img {
        height: 100%;
        object-fit: contain;
        width: 100%;
    }

    .mic-share-row {
        align-items: center;
        display: flex;
        font-size: 18px;
        gap: 10px;
        margin-top: 22px;
    }

    .mic-share-dot {
        align-items: center;
        background: #1677f2;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        font-weight: 700;
        height: 28px;
        justify-content: center;
        text-transform: uppercase;
        width: 28px;
    }

    .mic-detail-info h3 {
        font-size: 24px;
        line-height: 1.25;
        margin: 0 34px 12px 0;
    }

    .mic-detail-rating {
        align-items: center;
        color: #666;
        display: flex;
        gap: 16px;
        margin-bottom: 12px;
    }

    .mic-detail-rating span:nth-child(2) {
        color: #F88379;
    }

    .mic-detail-price {
        background: #fafafa;
        color: #F88379;
        font-size: 36px;
        margin-bottom: 22px;
        padding: 18px 24px;
    }

    .mic-detail-stock {
        color: #0f766e;
        font-size: 18px;
        font-weight: 700;
        margin: -10px 0 20px;
    }

    .mic-detail-stock.is-out {
        color: #b91c1c;
    }

    .mic-detail-stock.is-low {
        color: #dc2626;
    }

    .mic-detail-row {
        display: grid;
        gap: 16px;
        grid-template-columns: 120px 1fr;
        margin: 18px 0;
    }

    .mic-detail-row span,
    .mic-detail-cart label {
        color: #666;
    }

    .mic-detail-info p {
        color: #444;
        line-height: 1.5;
        margin: 22px 0;
    }

    .mic-detail-cart label {
        align-items: center;
        display: flex;
        gap: 24px;
        margin-bottom: 28px;
    }

    .mic-detail-cart input {
        border: 1px solid #ddd;
        height: 40px;
        text-align: center;
        width: 84px;
    }

    .mic-detail-actions {
        display: flex;
        gap: 16px;
    }

    .mic-detail-actions button {
        cursor: pointer;
        font-size: 16px;
        height: 60px;
        min-width: 225px;
    }

    .mic-outline-btn {
        background: #fff0f3;
        border: 1px solid #F88379;
        color: #F88379;
    }

    .mic-solid-btn {
        background: #F88379;
        border: 1px solid #F88379;
        color: #fff;
    }

    @media (max-width: 991.98px) {
        .mic-product-grid {
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }

        .mic-product-detail {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .mic-sortbar {
            align-items: stretch;
            overflow-x: auto;
            padding: 12px;
        }

        .mic-category-menu {
            min-width: 210px;
        }

        .mic-product-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .mic-product-title {
            font-size: 16px;
        }

        .mic-detail-actions {
            flex-direction: column;
        }

        .mic-detail-actions button {
            min-width: 0;
            width: 100%;
        }
    }
    /* Modern customer menu experience. */
    .mic-marketplace { background:linear-gradient(180deg, #fff7f5 0, #ffffff 330px); padding:48px 0 64px; }
    .mic-marketplace-inner { max-width:1320px; padding:0 24px; }
    .mic-menu-heading { align-items:end; display:flex; justify-content:space-between; margin:0 0 28px; }
    .mic-eyebrow { color:#e85c55; font-size:12px; font-weight:800; letter-spacing:.12em; }
    .mic-menu-heading h2 { color:#1f2937; font-size:36px; font-weight:800; letter-spacing:-.04em; margin:6px 0 8px; }
    .mic-menu-heading p { color:#64748b; margin:0; }
    .mic-menu-count { background:#fff; border:1px solid #fecaca; border-radius:999px; color:#b91c1c; font-size:13px; font-weight:800; padding:9px 14px; }
    .mic-sortbar { background:rgba(255,255,255,.92); border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 10px 24px rgba(15,23,42,.06); gap:10px; margin-bottom:28px; padding:12px; }
    .mic-sortbar > span { color:#475569; font-size:13px; font-weight:800; margin:0 4px; }
    .mic-category-filter summary, .mic-sort-btn, .mic-price-sort { background:#fff; border:1px solid #dbe3ee; border-radius:10px; color:#334155; font-weight:700; min-height:42px; }
    .mic-sort-btn:hover, .mic-sort-btn.is-active, .mic-category-filter[open] summary { background:#fff1f0; border-color:#f88379; color:#c2413a; }
    .mic-product-grid { gap:22px; grid-template-columns:repeat(auto-fill, minmax(245px, 1fr)); }
    .mic-product-card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 8px 22px rgba(15,23,42,.06); overflow:hidden; transition:transform .2s ease, box-shadow .2s ease; }
    .mic-product-card:hover { box-shadow:0 18px 36px rgba(15,23,42,.12); transform:translateY(-4px); }
    .mic-product-image-wrap { background:#fff7f5; height:205px; }
    .mic-product-image-wrap img { height:100%; object-fit:cover; width:100%; }
    .mic-product-ribbon { background:rgba(31,41,55,.82); border-radius:999px; left:12px; padding:5px 9px; top:12px; }
    .mic-product-body { padding:16px 16px 8px; }
    .mic-product-title { color:#1f2937; font-size:18px; font-weight:800; min-height:48px; }
    .mic-product-price { color:#e85c55; font-size:21px; font-weight:800; margin-top:10px; }
    .mic-stock { background:#f8fafc; border-radius:8px; padding:6px 8px; width:max-content; }
    .mic-product-meta { color:#64748b; margin-top:12px; }
    .mic-location { color:#64748b; font-size:12px; }
    .mic-card-cart { gap:10px; padding:12px 16px 16px; }
    .mic-card-cart input { border:1px solid #dbe3ee; border-radius:9px; height:44px; width:62px; }
    .mic-card-cart button { background:linear-gradient(135deg, #f88379, #ef5f62); border-radius:9px; box-shadow:0 8px 16px rgba(239,95,98,.18); font-weight:800; height:44px; }
    .mic-card-cart button:hover { filter:brightness(.96); }
    .mic-marketplace { background:#f8faf5; }
    .mic-menu-hero { align-items:center; background:linear-gradient(120deg, #fff 0 50%, #83bd47 50%); border-radius:28px; display:grid; gap:24px; grid-template-columns:1.05fr .95fr; margin-bottom:42px; min-height:350px; overflow:hidden; padding:42px 54px; position:relative; }
    .mic-menu-hero-copy { max-width:430px; position:relative; z-index:1; }
    .mic-eyebrow { color:#72a83d; }
    .mic-menu-hero h2 { color:#182014; font-size:48px; font-weight:900; letter-spacing:-.055em; line-height:.98; margin:12px 0 16px; }
    .mic-menu-hero p { color:#64705f; line-height:1.7; margin:0 0 24px; max-width:360px; }
    .mic-hero-cta { background:#7fb545; border-radius:999px; color:#fff; display:inline-flex; font-size:14px; font-weight:800; gap:10px; padding:13px 20px; text-decoration:none; }
    .mic-hero-cta:hover { background:#679b34; color:#fff; text-decoration:none; }
    .mic-menu-hero-dish { align-items:center; display:flex; justify-content:center; position:relative; }
    .mic-menu-hero-dish::before { background:#fff; border:10px solid rgba(255,255,255,.6); border-radius:50%; content:''; height:270px; position:absolute; width:270px; }
    .mic-menu-hero-dish img { border-radius:50%; height:250px; object-fit:cover; position:relative; width:250px; }
    .mic-menu-hero .mic-menu-count { background:rgba(255,255,255,.9); border:0; bottom:22px; color:#507b28; position:absolute; right:22px; }
    .mic-menu-heading { margin-bottom:18px; }
    .mic-menu-heading h2 { color:#1b2515; font-size:30px; font-weight:900; letter-spacing:-.04em; margin:5px 0 0; }
    .mic-sortbar { border-radius:14px; box-shadow:none; }
    .mic-category-filter summary, .mic-sort-btn, .mic-price-sort { border-radius:999px; }
    .mic-sort-btn:hover, .mic-sort-btn.is-active, .mic-category-filter[open] summary { background:#eef7e5; border-color:#83bd47; color:#507b28; }
    .mic-product-card { border:0; border-radius:18px; box-shadow:0 8px 22px rgba(47,68,28,.1); text-align:center; }
    .mic-product-image-wrap { align-items:center; background:transparent; display:flex; height:190px; justify-content:center; padding:20px 20px 0; }
    .mic-product-image-wrap img { border:7px solid #83bd47; border-radius:50%; height:156px; object-fit:cover; width:156px; }
    .mic-product-ribbon { display:none; }
    .mic-product-body { align-items:center; display:flex; flex-direction:column; padding-top:10px; }
    .mic-product-title { min-height:auto; }
    .mic-product-price { color:#5c9032; font-size:17px; }
    .mic-stock { margin-top:8px; }
    .mic-product-meta { width:100%; }
    .mic-location { display:none; }
    .mic-card-cart { justify-content:center; }
    .mic-card-cart button { background:#83bd47; box-shadow:none; }
    .mic-card-cart button:hover { background:#679b34; }
    /* Spacious menu item detail view inspired by modern healthy-food stores. */
    .mic-product-modal-backdrop { background:rgba(29,43,20,.42); backdrop-filter:blur(4px); }
    .mic-product-detail { border-radius:24px; box-shadow:0 28px 70px rgba(21,34,13,.25); display:grid; grid-template-columns:1fr 1fr; max-width:1040px; overflow:hidden; }
    .mic-detail-media { align-items:center; background:linear-gradient(145deg, #f2f7ec, #fff); display:flex; justify-content:center; min-height:560px; padding:50px; position:relative; }
    .mic-detail-media > img { background:#fff; border:10px solid #83bd47; border-radius:50%; box-shadow:0 20px 40px rgba(59,88,31,.18); height:360px; object-fit:cover; width:360px; }
    .mic-detail-thumbs, .mic-share-row { display:none; }
    .mic-detail-info { align-self:center; padding:52px; }
    .mic-detail-info h3 { color:#1b2515; font-size:38px; font-weight:900; letter-spacing:-.05em; line-height:1.05; }
    .mic-detail-rating span { color:#83bd47; }
    .mic-detail-price { color:#5c9032; font-size:30px; font-weight:900; }
    .mic-detail-stock { color:#5c9032; font-weight:800; }
    .mic-detail-row { border-color:#e5ebdf; }
    .mic-detail-info p { color:#64705f; line-height:1.7; }
    .mic-detail-cart label { color:#526245; font-weight:800; }
    .mic-detail-cart input { border-color:#d7e5ca; border-radius:9px; }
    .mic-solid-btn { background:#83bd47 !important; border-radius:9px !important; }
    .mic-outline-btn { border-color:#83bd47 !important; border-radius:9px !important; color:#5c9032 !important; }
    .mic-product-close { background:#fff; border-radius:50%; box-shadow:0 5px 15px rgba(15,23,42,.12); color:#526245; height:40px; right:20px; top:20px; width:40px; z-index:2; }
    /* Dark restaurant menu workspace. */
    .mic-marketplace { background:#f6e5af; padding:64px 0; }
    .mic-marketplace-inner { background:radial-gradient(circle at 20% 10%, rgba(255,255,255,.08), transparent 25%), #111312; border-radius:34px; box-shadow:0 28px 58px rgba(70,49,10,.22); max-width:1360px; padding:42px; }
    .mic-menu-hero, .mic-menu-heading { display:none; }
    .mic-sortbar { background:transparent; border:0; border-bottom:1px solid rgba(255,255,255,.18); border-radius:0; box-shadow:none; margin-bottom:30px; padding:0 0 20px; }
    .mic-sortbar > span { color:#f5f5f4; font-size:25px; font-weight:900; margin-right:auto; }
    .mic-category-filter summary, .mic-sort-btn, .mic-price-sort { background:transparent; border-color:rgba(255,255,255,.45); color:#fff; }
    .mic-sort-btn:hover, .mic-sort-btn.is-active, .mic-category-filter[open] summary { background:#f6c63f; border-color:#f6c63f; color:#1d1d1b; }
    .mic-page-count { color:#f6c63f; }
    .mic-product-grid { column-gap:42px; grid-template-columns:repeat(2, minmax(0, 1fr)); row-gap:8px; }
    .mic-product-card { align-items:center; background:transparent; border:0; border-radius:0; box-shadow:none; display:grid; grid-template-columns:112px 1fr; min-height:132px; padding:12px 0; text-align:left; }
    .mic-product-card:hover { background:rgba(255,255,255,.04); box-shadow:none; transform:none; }
    .mic-product-open { align-items:center; display:contents; }
    .mic-product-image-wrap { align-items:center; background:transparent; display:flex; grid-row:1 / span 2; height:104px; justify-content:center; padding:0; }
    .mic-product-image-wrap img { border:0; border-radius:15px; height:104px; object-fit:cover; width:104px; }
    .mic-product-body { align-items:start; display:flex; flex-direction:column; padding:0 12px; }
    .mic-product-title { color:#fff; font-size:17px; min-height:0; }
    .mic-product-price { color:#f6c63f; font-size:16px; margin-top:6px; }
    .mic-stock { background:transparent; color:#d1d5db !important; font-size:12px; margin-top:5px; padding:0; }
    .mic-product-meta { color:#f6c63f; font-size:11px; margin-top:7px; }
    .mic-product-meta span:last-child { color:#cbd5e1; }
    .mic-card-cart { grid-column:2; justify-content:flex-start; padding:4px 12px 0; }
    .mic-card-cart input { background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.25); color:#fff; height:34px; width:52px; }
    .mic-card-cart button { background:#f6c63f; border-radius:7px; box-shadow:none; color:#1d1d1b; height:34px; padding:0 14px; }
    .mic-card-cart button:hover { background:#ffdc6c; }
    /* Product-only menu cards. */
    .mic-product-grid { grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); }
    .mic-product-card { background:#fff; border-radius:16px; display:block; min-height:0; overflow:hidden; padding:0; }
    .mic-product-image-wrap { background:#fff; display:block; height:250px; padding:0; }
    .mic-product-image-wrap img { border:0; border-radius:0; display:block; height:100%; object-fit:contain; width:100%; }
    .mic-product-body, .mic-card-cart { display:none; }
    .mic-product-detail { grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); max-height:90vh; overflow:auto; width:calc(100vw - 48px); }
    .mic-detail-media { min-width:0; }
    .mic-detail-media > img { height:auto; max-height:60vh; max-width:100%; object-fit:contain; width:100%; }
    .mic-detail-info { min-width:0; overflow-wrap:anywhere; }
    /* Keep the menu as a steady image gallery—no product pop-up or internal scrollbars. */
    .mic-product-modal { display:none !important; }
    .mic-product-open { cursor:default; pointer-events:none; }
    /* Restore the dark restaurant menu layout. */
    .mic-marketplace { background:#f6e5af; }
    .mic-marketplace-inner { background:radial-gradient(circle at 20% 10%, rgba(255,255,255,.08), transparent 25%), #111312; }
    .mic-sortbar { display:flex; }
    .mic-product-grid { column-gap:42px; grid-template-columns:repeat(2, minmax(0, 1fr)); row-gap:8px; }
    .mic-product-card { align-items:center; background:transparent; border:0; border-radius:0; display:grid; grid-template-columns:112px 1fr; min-height:132px; overflow:visible; padding:12px 0; text-align:left; }
    .mic-product-image-wrap { background:transparent; border-radius:0; display:flex; grid-row:1 / span 2; height:104px; padding:0; }
    .mic-product-image-wrap img { border-radius:15px; height:104px; object-fit:cover; width:104px; }
    .mic-product-body { align-items:start; display:flex; flex-direction:column; padding:0 12px; }
    .mic-product-title { color:#fff; font-size:17px; min-height:0; }
    .mic-product-price { color:#f6c63f; font-size:16px; }
    .mic-stock { display:block; }
    .mic-product-meta { display:flex; }
    .mic-card-cart { display:flex; grid-column:2; justify-content:flex-start; padding:4px 12px 0; }
    .mic-card-cart input { background:rgba(255,255,255,.06); color:#fff; height:34px; width:52px; }
    .mic-card-cart button { background:#f6c63f; color:#1d1d1b; height:34px; }
    .mic-product-open { cursor:pointer; pointer-events:auto; }
    /* Restored product detail popup, sized to the viewport without horizontal scrolling. */
    .mic-product-modal.is-open { display:flex !important; }
    .mic-product-detail { height:auto; max-height:calc(100vh - 48px); max-width:1120px; overflow-x:hidden; overflow-y:auto; width:calc(100vw - 48px); }
    .mic-detail-media { min-height:0; padding:32px; }
    .mic-detail-media > img { height:auto; max-height:calc(100vh - 130px); object-fit:contain; width:100%; }
    .mic-detail-info { padding:34px; }
    .mic-detail-info p { -webkit-box-orient:vertical; -webkit-line-clamp:4; display:-webkit-box; overflow:hidden; }
    .mic-detail-actions { flex-wrap:wrap; }
    .mic-detail-actions button { min-width:0; }
    /* Keep the full product description and a clean, stable dialog. */
    .mic-product-modal.is-open { align-items:center; display:flex !important; justify-content:center; overflow:hidden; }
    .mic-product-detail { margin:0; overflow-x:hidden; scrollbar-width:none; }
    .mic-product-detail::-webkit-scrollbar { display:none; }
    .mic-detail-info p { -webkit-line-clamp:unset; display:block; max-height:none; overflow:visible; }
    .mic-detail-actions { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; width:100%; }
    .mic-detail-actions button { font-size:16px; min-width:0; padding:0 16px; width:100%; }
    @media (max-width:640px) { .mic-product-grid { grid-template-columns:1fr; } .mic-product-card { display:grid; } .mic-product-image-wrap { display:flex; height:104px; } .mic-product-image-wrap img { height:104px; width:104px; } .mic-product-body, .mic-card-cart { display:flex; } }
    @media (max-width:640px) { .mic-marketplace { padding-top:24px; } .mic-marketplace-inner { padding:0 16px; } .mic-menu-hero { background:linear-gradient(160deg, #fff 0 55%, #83bd47 55%); grid-template-columns:1fr; min-height:520px; padding:32px 26px; } .mic-menu-hero h2 { font-size:40px; } .mic-menu-hero-dish::before { height:220px; width:220px; } .mic-menu-hero-dish img { height:200px; width:200px; } .mic-menu-heading { align-items:start; flex-direction:column; gap:14px; } .mic-menu-heading h2 { font-size:28px; } .mic-sortbar { align-items:stretch; flex-wrap:wrap; } .mic-page-count { display:none; } .mic-product-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; } .mic-product-image-wrap { height:145px; } .mic-product-image-wrap img { border-width:5px; height:118px; width:118px; } .mic-product-body { padding:12px 12px 4px; } .mic-product-title { font-size:15px; min-height:42px; } .mic-card-cart { padding:10px 12px 12px; } .mic-card-cart input { width:46px; } .mic-product-detail { display:block; max-height:92vh; overflow:auto; } .mic-detail-media { min-height:300px; padding:28px; } .mic-detail-media > img { height:220px; width:220px; } .mic-detail-info { padding:30px 24px; } .mic-detail-info h3 { font-size:30px; } }
    /* Image-first mobile menu and a clean white product preview. */
    .mic-detail-media { background:#fff; }
    .mic-detail-media > img { border:0; border-radius:18px; box-shadow:none; height:min(430px, 38vw); object-fit:contain; width:min(430px, 38vw); }
    /* Product preview sits over the existing menu; only the dialog itself may scroll. */
    .mic-product-modal,
    .mic-product-modal.is-open { align-items:center; display:none; inset:0; justify-content:center; position:fixed; z-index:4000; }
    .mic-product-modal.is-open { display:flex !important; }
    .mic-product-modal-backdrop { background:rgba(14, 20, 16, .46); backdrop-filter:blur(5px); position:fixed; }
    .mic-product-detail { box-sizing:border-box; margin:0; max-height:calc(100vh - 48px); overflow:hidden; width:min(1120px, calc(100vw - 48px)); }
    .mic-detail-info { max-height:calc(100vh - 48px); overflow-y:auto; scrollbar-width:none; }
    .mic-detail-info::-webkit-scrollbar { display:none; }
    /* A larger, simple two-item menu without sorting dropdowns. */
    .mic-product-grid { column-gap:64px; grid-template-columns:repeat(2, minmax(0, 1fr)); row-gap:24px; }
    .mic-product-card { grid-template-columns:180px minmax(0, 1fr); min-height:210px; padding:18px 0; }
    .mic-product-image-wrap { height:170px; }
    .mic-product-image-wrap img { height:170px; width:170px; }
    .mic-product-title { font-size:22px; }
    .mic-product-price { font-size:20px; }
    .mic-stock { font-size:15px; }
    .mic-product-meta { font-size:14px; }
    .mic-card-cart button { height:42px; padding:0 24px; }
    /* Light dashboard surface and larger two-column food cards. */
    .mic-marketplace { background:#f6f8fb; }
    .mic-marketplace-inner { background:#fff; border:1px solid #e5e7eb; box-shadow:0 18px 45px rgba(15,23,42,.08); max-width:1500px; }
    .mic-product-card { grid-template-columns:245px minmax(0, 1fr); min-height:280px; }
    .mic-product-image-wrap { height:235px; }
    .mic-product-image-wrap img { height:235px; width:235px; }
    .mic-product-title { color:#172033; font-size:25px; }
    .mic-product-price { color:#ef5d5d; font-size:22px; }
    .mic-product-description { color:#64748b; display:block; font-size:14px; line-height:1.45; margin-top:8px; }
    .mic-product-meta span:last-child, .mic-product-meta { color:#64748b; }
    .mic-card-cart input { background:#fff; border-color:#dbe2ea; color:#172033; }
    .mic-card-cart button { background:#f56060; color:#fff; }
    body.mic-modal-open { overflow:hidden; }
    .mic-product-modal-backdrop { background:rgba(15,23,42,.20); backdrop-filter:none; }
    .mic-product-detail { max-width:1040px; }
    @media (max-width:700px) {
        .mic-marketplace-inner { border-radius:20px; padding:20px 16px; }
        .mic-product-grid { grid-template-columns:1fr; }
        .mic-product-card { grid-template-columns:145px minmax(0, 1fr); min-height:180px; }
        .mic-product-image-wrap, .mic-product-image-wrap img { height:132px; width:132px; }
        .mic-product-title { font-size:19px; }
        .mic-product-description { -webkit-box-orient:vertical; -webkit-line-clamp:2; display:-webkit-box; overflow:hidden; }
        .mic-product-modal.is-open { padding:16px; }
        .mic-product-detail { display:block; max-height:calc(100vh - 32px); width:calc(100vw - 32px); }
        .mic-detail-media { padding:16px; }
        .mic-detail-media > img { height:auto; max-height:34vh; width:100%; }
        .mic-detail-info { max-height:calc(66vh - 32px); overflow-y:auto; padding:22px; }
    }
    /* Keep the actual menu visible behind a focused product preview. */
    .mic-product-modal-backdrop { background:rgba(15,23,42,.38) !important; backdrop-filter:blur(3px) !important; }
    .mic-product-detail { max-width:min(960px, calc(100vw - 64px)) !important; }
    .mic-detail-media > img { height:min(470px, 42vw); max-height:calc(100vh - 120px); width:min(470px, 42vw); }
    @media (max-width:700px) {
        .mic-product-detail { max-width:calc(100vw - 32px) !important; }
        .mic-detail-media > img { height:auto; max-height:36vh; width:100%; }
    }
    /* Show the complete food artwork, including its printed product name and price. */
    .mic-product-card { grid-template-columns:265px minmax(0, 1fr); min-height:300px; }
    .mic-product-image-wrap { height:250px; }
    .mic-product-image-wrap img { background:#fff; height:250px; object-fit:contain; width:250px; }
    @media (max-width:700px) {
        .mic-product-card { grid-template-columns:165px minmax(0, 1fr); min-height:200px; }
        .mic-product-image-wrap, .mic-product-image-wrap img { height:150px; width:150px; }
    }
    /* Explicit layers: the modal never replaces the menu page behind it. */
    .mic-product-modal { background:transparent !important; }
    .mic-product-modal-backdrop { background:transparent !important; display:none; inset:0; z-index:0; }
    .mic-product-detail { background:rgba(255,255,255,.97); position:relative; z-index:1; }
    .mic-menu-modal-background { background:#f6f8fb; inset:0; overflow:hidden; pointer-events:none; position:absolute; z-index:0; }
    .mic-menu-modal-background > * { filter:blur(2px); opacity:.52; transform:scale(.96); transform-origin:center top; }
    @media (max-width:640px) {
        .mic-marketplace-inner { background:#111312; border-radius:20px; padding:20px 14px; }
        .mic-sortbar { display:none; }
        .mic-product-grid { grid-template-columns:1fr; gap:16px; }
        .mic-product-card { background:transparent; display:block; min-height:0; padding:0; }
        .mic-product-image-wrap { background:#fff; border-radius:16px; display:block; height:auto; padding:0; }
        .mic-product-image-wrap img { border:0; border-radius:16px; display:block; height:auto; max-height:none; object-fit:cover; width:100%; }
        .mic-product-body, .mic-card-cart { display:none; }
        .mic-detail-media { background:#fff; min-height:0; padding:20px; }
        .mic-detail-media > img { border:0; border-radius:16px; height:auto; max-height:58vh; object-fit:contain; width:100%; }
    }

    /* Final menu card and stock-status rules. */
    .mic-stock.is-low,
    .mic-stock.is-out,
    .mic-detail-stock.is-low,
    .mic-detail-stock.is-out {
        color: #dc2626 !important;
    }

    .mic-stock.is-available,
    .mic-detail-stock:not(.is-low):not(.is-out) {
        color: #15803d !important;
    }

    .mic-product-grid {
        gap: 24px;
    }

    .mic-product-card {
        align-items: start;
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
        grid-template-columns: minmax(0, 260px) minmax(0, 1fr);
        min-height: 0;
        overflow: hidden;
        padding: 18px;
    }

    .mic-product-card:hover {
        box-shadow: 0 14px 30px rgba(15, 23, 42, .13);
        transform: translateY(-2px);
    }

    .mic-product-image-wrap {
        align-items: center;
        aspect-ratio: 285 / 488;
        background: #fff;
        border: 0;
        box-sizing: border-box;
        display: flex;
        flex: 0 0 260px;
        height: 445px;
        overflow: hidden;
        padding: 0;
        position: relative;
        justify-content: center;
        width: 260px;
    }

    .mic-product-image-wrap img {
        background: transparent !important;
        box-sizing: border-box;
        display: block;
        filter: sepia(.16) saturate(.92) contrast(1.03);
        height: 100% !important;
        object-fit: contain !important;
        position: relative;
        transform: none;
        transform-origin: center;
        width: 100% !important;
        z-index: 1;
    }

    @media (max-width:640px) {
        .mic-product-card {
            background: #fff;
            border: 1px solid #e4e7ec;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
            grid-template-columns: 130px minmax(0, 1fr);
            padding: 14px;
        }

        .mic-product-image-wrap {
            flex-basis: 130px;
            height: 223px;
            width: 130px;
        }

    }

    .mic-product-body { font-family:Arial, Helvetica, sans-serif; }
    .mic-product-title { font-family:Arial, Helvetica, sans-serif; font-size:28px; font-weight:900; letter-spacing:-.035em; line-height:1.08; }
    .mic-product-price { font-family:Arial, Helvetica, sans-serif; font-size:24px; font-weight:900; }
    .mic-product-description { font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:600; line-height:1.45; }
    .mic-stock { font-family:Arial, Helvetica, sans-serif; font-size:16px; }
    .mic-product-meta { font-family:Arial, Helvetica, sans-serif; font-size:15px; font-weight:700; justify-content:flex-start; margin-top:4px; }
    .mic-card-cart { align-items:center; gap:14px; }
    .mic-card-cart > button { border-radius:10px; flex:1; font-size:16px; font-weight:800; height:46px; }
    @media (max-width:700px) { .mic-product-title { font-size:22px; } .mic-product-description { font-size:14px; } }

    /* Final phone layout: retain the full food-card information at a readable size. */
    @media (max-width:640px) {
        .mic-marketplace { background:#f6f8fb; padding:18px 0 32px; }
        .mic-marketplace-inner { background:#fff; border:1px solid #e4e7ec; border-radius:18px; padding:14px; }
        .mic-sortbar { display:flex; margin-bottom:14px; }
        .mic-product-grid { display:grid; gap:14px; grid-template-columns:1fr; }
        .mic-product-card { align-items:start; background:#fff; border:1px solid #e4e7ec; border-radius:14px; box-shadow:0 6px 16px rgba(15,23,42,.08); display:grid; grid-template-columns:120px minmax(0,1fr); min-height:0; overflow:hidden; padding:12px; }
        .mic-product-open { display:contents; pointer-events:auto; }
        .mic-product-image-wrap { align-items:center; aspect-ratio:auto; background:#fff; display:flex; grid-row:1 / span 2; height:205px; justify-content:center; overflow:hidden; padding:0; width:120px; }
        .mic-product-image-wrap img { border-radius:10px; display:block; height:100% !important; max-height:none; object-fit:contain !important; width:100% !important; }
        .mic-product-body { align-items:flex-start; display:flex; flex-direction:column; padding:0 0 0 12px; }
        .mic-product-title { color:#172033; display:block; font-size:19px; line-height:1.1; min-height:0; }
        .mic-product-price { color:#ef5d5d; display:block; font-size:18px; }
        .mic-product-description { -webkit-box-orient:vertical; -webkit-line-clamp:2; color:#475569; display:-webkit-box; font-size:13px; line-height:1.35; overflow:hidden; }
        .mic-stock, .mic-product-meta { display:flex; font-size:12px; }
        .mic-card-cart { align-items:center; display:flex; gap:8px; grid-column:2; padding:7px 0 0 12px; }
        .mic-card-cart input { height:34px; width:42px; }
        .mic-card-cart > button { font-size:12px; height:36px; padding:0 9px; }
    }

    /* Full-width menu: every food keeps its own clear card. */
    .mic-menu-hero,
    .mic-menu-heading { display:none; }
    .mic-marketplace { padding:0 24px 40px; }
    .mic-marketplace-inner { border-left:0; border-radius:0; border-right:0; max-width:none; width:100%; }
    .mic-product-grid { grid-template-columns:repeat(2, minmax(0, 1fr)) !important; }
    .mic-product-card { grid-template-columns:minmax(0, 300px) minmax(0, 1fr); width:100%; }
    .mic-product-image-wrap { flex-basis:300px; height:515px; width:300px; }

    @media (max-width:640px) {
        .mic-marketplace { padding:0 12px 28px; }
        .mic-marketplace-inner { border:0; border-radius:0; padding:12px 0; }
        .mic-product-grid { grid-template-columns:1fr !important; }
        .mic-product-card { grid-template-columns:120px minmax(0, 1fr); }
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const grid = document.getElementById('micProductGrid');
        const modal = document.getElementById('micProductModal');
        if (!grid || !modal) return;

        const menuPreview = modal.querySelector('.mic-menu-modal-background');
        const menuSurface = document.querySelector('.mic-marketplace-inner');

        const cards = Array.from(grid.querySelectorAll('.mic-product-card'));
        const sortButtons = document.querySelectorAll('.mic-sort-btn');
        const priceSort = document.querySelector('.mic-price-sort');
        const categoryFilter = document.querySelector('.mic-category-filter');
        const categoryButtons = document.querySelectorAll('.mic-category-menu button');
        const categoryLabel = document.getElementById('micCategoryLabel');
        const emptyCategory = document.getElementById('micEmptyCategory');
        const modalImage = document.getElementById('micProductImage');
        const modalThumb = document.getElementById('micProductThumb');
        const modalTitle = document.getElementById('micProductTitle');
        const modalPrice = document.getElementById('micProductPrice');
        const modalStock = document.getElementById('micProductStock');
        const modalDetail = document.getElementById('micProductDetail');
        const modalForm = document.getElementById('micProductForm');
        const addCartButton = document.getElementById('micAddCartButton');
        const buyNowButton = document.getElementById('micBuyNowButton');
        let activeCategory = 'all';

        function updateVisibleCards() {
            let visibleCount = 0;

            cards.forEach(function (card) {
                const showCard = activeCategory === 'all' || card.dataset.category === activeCategory;
                card.classList.toggle('is-hidden', !showCard);
                if (showCard) visibleCount++;
            });

            if (emptyCategory) {
                emptyCategory.classList.toggle('is-visible', visibleCount === 0);
            }
        }

        function sortCards(type) {
            const sorted = [...cards].sort(function (a, b) {
                if (type === 'latest') return Number(b.dataset.date) - Number(a.dataset.date);
                if (type === 'sales') return Number(b.dataset.sold) - Number(a.dataset.sold);
                if (type === 'price-asc') return Number(a.dataset.price) - Number(b.dataset.price);
                if (type === 'price-desc') return Number(b.dataset.price) - Number(a.dataset.price);
                return cards.indexOf(a) - cards.indexOf(b);
            });

            sorted.forEach(card => grid.appendChild(card));
            updateVisibleCards();
        }

        categoryButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activeCategory = button.dataset.category || 'all';
                categoryButtons.forEach(item => item.classList.remove('is-active'));
                button.classList.add('is-active');

                if (categoryLabel) {
                    categoryLabel.textContent = activeCategory === 'all' ? 'All' : button.textContent.trim();
                }

                if (categoryFilter) {
                    categoryFilter.open = false;
                }

                updateVisibleCards();
            });
        });

        sortButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                sortButtons.forEach(item => item.classList.remove('is-active'));
                button.classList.add('is-active');
                if (priceSort) priceSort.value = '';
                sortCards(button.dataset.sort);
            });
        });

        if (priceSort) {
            priceSort.addEventListener('change', function () {
                sortButtons.forEach(item => item.classList.remove('is-active'));
                sortCards(priceSort.value || 'relevance');
            });
        }

        updateVisibleCards();

        // Add items without leaving the menu. Buy Now intentionally retains
        // its normal checkout navigation.
        document.addEventListener('submit', async function (event) {
            const form = event.target;
            if (!form.matches('.mic-card-cart, #micProductForm')) return;

            const submitter = event.submitter;
            if (submitter && submitter.name === 'buy_now') return;

            event.preventDefault();

            const addUrl = form.action.replace('/add_cart/', '/add_cart_ajax/');
            const submitButton = submitter || form.querySelector('button[type="submit"]');
            const originalLabel = submitButton ? submitButton.textContent : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Adding...';
            }

            try {
                const response = await fetch(addUrl, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'Unable to add this item to your cart.');

                document.dispatchEvent(new CustomEvent('cart:updated', {
                    detail: { count: result.cart_count }
                }));
            } catch (error) {
                window.alert(error.message || 'Unable to add this item to your cart.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalLabel;
                }
            }
        });

        grid.addEventListener('click', function (event) {
            const opener = event.target.closest('.mic-product-open');
            if (!opener) return;

            const card = opener.closest('.mic-product-card');
            const stock = Number(card.dataset.stock);
            const price = Number(card.dataset.price).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            modalImage.src = card.dataset.image;
            modalThumb.src = card.dataset.image;
            modalImage.alt = card.dataset.title;
            modalTitle.textContent = card.dataset.title;
            modalPrice.textContent = '\u20b1' + price;
            modalStock.textContent = stock > 0 ? stock + ' available stock' : 'Out of stock';
            modalStock.classList.toggle('is-out', stock <= 0);
            modalStock.classList.toggle('is-low', stock > 0 && stock < 5);
            modalDetail.textContent = card.dataset.detail || 'No extra details saved in the system.';
            modalForm.action = card.dataset.action;
            addCartButton.disabled = stock <= 0;
            buyNowButton.disabled = stock <= 0;
            if (menuPreview && menuSurface && !menuPreview.hasChildNodes()) {
                menuPreview.appendChild(menuSurface.cloneNode(true));
            }
            modal.classList.add('is-open');
            modal.style.display = 'flex';
            document.body.classList.add('mic-modal-open');
            modal.setAttribute('aria-hidden', 'false');
        });

        modal.addEventListener('click', function (event) {
            if (!event.target.closest('[data-close-product]')) return;
            modal.classList.remove('is-open');
            modal.style.display = '';
            document.body.classList.remove('mic-modal-open');
            modal.setAttribute('aria-hidden', 'true');
        });
    });
</script>
