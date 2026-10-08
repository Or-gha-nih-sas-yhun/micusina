@php
    $cartBadgeCount = Auth::check()
        ? \App\Models\Cart::where('userid', Auth::id())->sum('quantity')
        : array_sum(array_column(session('guest_cart', []), 'quantity'));
    $userInitial = Auth::check() ? strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) : 'U';
@endphp

<style>
    /* Icon-first tracking links retain visible text while providing an accessible,
       keyboard-friendly tooltip on both header variants. */
    .track-action {
        align-items: center;
        display: inline-flex;
        justify-content: center;
        position: relative;
    }

    .track-action-label { display: none; }

    .track-action::after {
        background: #fff;
        border: 1px solid #F88379;
        border-radius: 6px;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .14);
        color: #1f2937;
        content: attr(data-tooltip);
        font-size: 12px;
        font-weight: 700;
        left: 50%;
        opacity: 0;
        padding: 6px 9px;
        pointer-events: none;
        position: absolute;
        top: calc(100% + 9px);
        transform: translate(-50%, -4px);
        transition: opacity .18s ease, transform .18s ease;
        white-space: nowrap;
        z-index: 1100;
    }

    .track-action:hover::after,
    .track-action:focus-visible::after {
        opacity: 1;
        transform: translate(-50%, 0);
    }

    .track-action:focus-visible { outline: 2px solid #F88379; outline-offset: 3px; }

    @media (max-width: 767px) {
        .track-action::after { left: 0; transform: translate(0, -4px); }
        .track-action:hover::after,
        .track-action:focus-visible::after { transform: translate(0, 0); }
    }
</style>

@if(!request('section') && empty($forceInnerNavbar))
<style>
    body.front-only {
        background: #000;
        margin: 0;
    }

    .burger-front {
        align-items: center;
        background: #000;
        display: flex;
        min-height: 100vh;
        padding: 0;
    }

    .burger-panel {
        background:
            radial-gradient(circle at 82% 48%, rgba(39, 53, 62, .5), transparent 36%),
            linear-gradient(90deg, #020306 0%, #05080c 54%, #020306 100%);
        border-radius: 8px;
        box-shadow: none;
        display: grid;
        grid-template-columns: 54% 46%;
        min-height: 100vh;
        overflow: hidden;
        position: relative;
        width: 100%;
    }

    .burger-topbar {
        align-items: center;
        display: grid;
        grid-template-columns: 260px 1fr 260px;
        left: 7vw;
        position: absolute;
        right: 7vw;
        top: 55px;
        z-index: 5;
    }

    .burger-mark {
        display: inline-flex;
        width: 86px;
    }

    .burger-mark img {
        display: block;
        height: auto;
        width: 100%;
    }

    .burger-nav {
        align-items: center;
        display: flex;
        gap: 44px;
        justify-content: center;
    }

    .burger-nav a {
        color: #fff;
        text-decoration: none;
    }

    .burger-nav a {
        font-size: 17px;
        font-weight: 500;
        line-height: 1;
    }

    .burger-nav a.active,
    .burger-nav a:hover {
        color: #F88379;
    }

    .burger-login {
        align-items: center;
        display: flex;
        gap: 24px;
        justify-content: flex-end;
    }

    .burger-login form {
        margin: 0;
    }

    .burger-login a,
    .burger-login button {
        background: transparent;
        border: 0;
        color: #fff;
        cursor: pointer;
        font-size: 17px;
        font-weight: 700;
        line-height: 1;
        padding: 0;
        text-decoration: none;
    }

    .burger-login a:hover,
    .burger-login button:hover {
        color: #F88379;
    }

    .burger-login .guest-auth-link {
        border-radius: 999px;
        color: #9b6b92;
        padding: 9px 15px;
    }

    .burger-login .guest-register-link {
        background: #9b6b92;
        color: #fff;
    }

    .burger-login .guest-auth-link:hover {
        background: #7f526f;
        color: #fff;
    }

    .cart-badge-link {
        align-items: center;
        display: inline-flex;
        gap: 7px;
    }

    .cart-badge {
        align-items: center;
        background: #F88379;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 12px;
        font-weight: 900;
        height: 20px;
        justify-content: center;
        line-height: 1;
        min-width: 20px;
        padding: 0 6px;
    }

    .front-user-menu {
        position: relative;
        z-index: 1001;
    }

    .front-user-menu summary {
        align-items: center;
        background: #eef3ff;
        border: 3px solid #fff;
        border-radius: 999px;
        box-shadow: 0 0 0 2px #1d2330;
        color: #F88379;
        cursor: pointer;
        display: inline-flex;
        font-size: 18px;
        font-weight: 800;
        height: 46px;
        justify-content: center;
        list-style: none;
        width: 46px;
    }

    .front-user-menu summary::-webkit-details-marker {
        display: none;
    }

    .front-user-menu summary img {
        border-radius: 999px;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .front-user-dropdown {
        background: #111;
        border: 1px solid #2b2b2b;
        border-radius: 8px;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .34);
        color: #fff;
        min-width: 230px;
        padding: 10px;
        position: absolute;
        right: 0;
        top: calc(100% + 12px);
        z-index: 1002;
        pointer-events: auto;
    }

    .front-user-info {
        border-bottom: 1px solid #2b2b2b;
        margin-bottom: 8px;
        padding: 8px 10px 12px;
    }

    .front-user-info strong,
    .front-user-info span {
        display: block;
    }

    .front-user-info strong {
        color: #fff;
        font-size: 15px;
    }

    .front-user-info span {
        color: #aaa;
        font-size: 13px;
        margin-top: 4px;
    }

    .front-user-dropdown form {
        margin: 0;
        width: 100%;
    }

    .front-user-dropdown button {
        background: transparent !important;
        border: 0 !important;
        border-radius: 6px;
        box-sizing: border-box;
        color: #fff !important;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        padding: 10px;
        text-align: left;
        width: 100%;
    }

    .front-user-dropdown .profile-action,
    .front-user-dropdown .home-action,
    .front-user-dropdown button.logout-action {
        background: #fff7f7 !important;
        border: 2px solid #ff746c !important;
        border-radius: 8px !important;
        box-sizing: border-box;
        box-shadow: inset 0 0 0 1px #ff746c;
        display: block;
        min-height: 48px;
    }

    .front-user-dropdown .profile-action,
    .front-user-dropdown .home-action { color: #202124 !important; margin-bottom: 8px; }

    .front-user-dropdown a {
        background: rgba(248, 131, 121, .08);
        border: 2px solid #F88379;
        border-radius: 6px;
        box-sizing: border-box;
        color: #fff;
        display: block;
        font-size: 14px;
        font-weight: 800;
        min-height: 48px;
        padding: 10px;
        text-decoration: none;
    }

    .front-user-dropdown .profile-action:hover,
    .front-user-dropdown .home-action:hover,
    .front-user-dropdown button.logout-action:hover { background: #ffecec !important; color: #050505 !important; }

    .front-user-dropdown a:not(.profile-action):not(.home-action):hover,
    .front-user-dropdown button:not(.logout-action):hover { background: #F88379; color: #050505; }

    .customer-photo-label {
        border-radius: 6px;
        color: #fff;
        cursor: pointer;
        display: block;
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        padding: 10px;
    }

    .customer-photo-label:hover { background: #F88379; color: #050505; }
    .customer-photo-error { color: #FFA69E; display: block; font-size: 11px; padding: 4px 10px; }

    .burger-copy {
        align-self: center;
        padding: 132px 0 118px 7vw;
        position: relative;
        z-index: 3;
    }

    .burger-copy h1 {
        color: #C8A2C8;
        font-family: Impact, "Arial Black", sans-serif;
        font-size: clamp(54px, 5vw, 88px);
        font-weight: 900;
        letter-spacing: 0;
        line-height: .9;
        margin: 0 0 30px;
        text-transform: uppercase;
    }

    .burger-copy h1 .headline-accent {
        color: inherit;
        text-shadow: 0 8px 28px rgba(200, 162, 200, .24);
    }

    .burger-copy p {
        color: #fff;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.25;
        margin: 0 0 29px;
        max-width: 640px;
    }

    .burger-actions {
        align-items: center;
        display: flex;
        gap: 54px;
    }

    .burger-primary,
    .burger-secondary {
        align-items: center;
        display: inline-flex;
        font-weight: 800;
        min-height: 52px;
        text-decoration: none;
    }

    .burger-primary {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .25);
        color: #F88379;
        justify-content: center;
        min-width: 170px;
        padding: 0 34px;
    }

    .burger-secondary {
        color: #F88379;
    }

    .burger-art {
        background: #020306;
        min-height: 100%;
        overflow: visible;
        position: relative;
    }

    .burger-art:before {
        background: radial-gradient(circle at 58% 50%, rgba(255, 33, 79, .10), transparent 42%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
        z-index: 0;
    }

    .burger-art img {
        filter: drop-shadow(0 34px 54px rgba(0, 0, 0, .52));
        height: 76%;
        max-height: none;
        max-width: none;
        object-fit: contain;
        object-position: center;
        position: absolute;
        right: -4%;
        top: 14%;
        width: 92%;
        z-index: 4;
    }

    .burger-art:after {
        background:
            linear-gradient(90deg, #05080c 0%, rgba(5, 8, 12, .92) 12%, rgba(5, 8, 12, .58) 31%, rgba(5, 8, 12, 0) 58%),
            linear-gradient(180deg, rgba(2, 3, 6, .42) 0%, rgba(2, 3, 6, 0) 28%, rgba(2, 3, 6, .58) 100%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
        z-index: 2;
    }

    .burger-thumbs {
        align-items: end;
        bottom: 46px;
        display: flex;
        gap: 24px;
        left: 7vw;
        position: absolute;
        z-index: 4;
    }

    .burger-thumb {
        align-items: center;
        appearance: none;
        background: rgba(255, 255, 255, .03);
        border: 1px solid rgba(255, 255, 255, .85);
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        height: 134px;
        justify-content: center;
        overflow: hidden;
        padding: 0;
        position: relative;
        width: 134px;
    }

    .burger-thumb img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .burger-thumb.active {
        border-color: #F88379;
        height: 166px;
        width: 166px;
    }

    .burger-thumb.active:before {
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 9px solid #F88379;
        content: "";
        left: 50%;
        position: absolute;
        top: -31px;
        transform: translateX(-50%);
    }

    @media (max-width: 991.98px) {
        .burger-panel {
            grid-template-columns: 1fr;
            min-height: 920px;
        }

        .burger-topbar {
            grid-template-columns: auto 1fr auto;
            left: 28px;
            right: 28px;
            top: 32px;
        }

        .burger-nav {
            gap: 18px;
        }

        .burger-copy {
            padding: 132px 28px 260px;
        }

        .burger-art {
            min-height: 360px;
        }

        .burger-art img {
            height: 400px;
            right: -120px;
            top: -30px;
            width: 760px;
        }

        .burger-thumbs {
            bottom: 28px;
            gap: 12px;
            left: 28px;
            max-width: calc(100% - 56px);
            overflow-x: auto;
        }

        .burger-thumb,
        .burger-thumb.active {
            flex: 0 0 82px;
            height: 82px;
            width: 82px;
        }
    }

    /* White-theme hero layout repair. */
    html, body.front-only { background:#fff !important; overflow-x:hidden; }
    html body .burger-front,
    html body .burger-panel,
    html body .burger-topbar,
    html body .burger-copy { background:#fff !important; }
    html body .burger-panel {
        grid-template-columns:minmax(0, 53%) minmax(0, 47%);
        min-height:100vh;
        overflow:hidden;
    }
    html body .burger-topbar {
        box-sizing:border-box;
        grid-template-columns:120px minmax(0, 1fr) minmax(190px, auto);
        left:clamp(28px, 6vw, 116px);
        right:clamp(28px, 6vw, 116px);
        top:34px;
        width:auto;
    }
    html body .burger-nav { gap:clamp(22px, 3vw, 48px); min-width:0; }
    html body .burger-login { gap:24px; min-width:0; white-space:nowrap; }
    html body .burger-mark { width:92px; }
    html body .burger-copy { padding:150px 5vw 250px 6vw; }
    html body .burger-copy h1 { font-size:clamp(52px, 4.5vw, 82px); }
    html body .burger-art {
        align-self:center;
        background:#fff !important;
        height:calc(100vh - 150px);
        margin:110px 26px 40px 0;
        min-height:560px;
        overflow:hidden;
    }
    html body .burger-art::before,
    html body .burger-art::after { background:none !important; display:none; }
    html body .burger-art img {
        border-radius:24px;
        filter:none;
        height:100%;
        inset:0;
        object-fit:contain;
        position:absolute;
        width:100%;
    }
    html body .burger-thumbs { bottom:40px; gap:16px; max-width:52%; }
    html body .burger-thumb,
    html body .burger-thumb.active {
        background:#fff !important;
        border:2px solid #e5e7eb;
        border-radius:14px;
        flex:0 0 126px;
        height:126px;
        width:126px;
    }
    html body .burger-thumb.active { border-color:#f25f5c !important; box-shadow:0 10px 24px rgba(242,95,92,.2); }
    html body.front-only .burger-nav a,
    html body.front-only .burger-login a,
    html body.front-only .burger-login button,
    html body.front-only .burger-copy h1,
    html body.front-only .burger-copy h1 span,
    html body.front-only .burger-copy p,
    html body.front-only .burger-primary,
    html body.front-only .burger-secondary {
        color:#000 !important;
    }
    html body.front-only .burger-primary {
        background:#fff !important;
        border:1px solid #d1d5db !important;
        color:#000 !important;
    }
    html body.front-only .burger-copy h1,
    html body.front-only .burger-copy h1 span {
        color:#C8A2C8 !important;
    }

    @media (max-width:1199.98px) {
        html body .burger-topbar { grid-template-columns:90px 1fr auto; }
        html body .burger-nav { gap:18px; }
        html body .burger-login { gap:14px; }
        html body .burger-copy { padding-left:4vw; }
        html body .burger-thumbs { left:4vw; }
        html body .burger-thumb, html body .burger-thumb.active { flex-basis:96px; height:96px; width:96px; }
    }

    @media (max-width:991.98px) {
        html body .burger-panel { display:block; min-height:100vh; overflow:visible; }
        html body .burger-topbar { align-items:flex-start; grid-template-columns:auto 1fr auto; }
        html body .burger-nav { flex-wrap:wrap; }
        html body .burger-copy { padding:150px 28px 28px; }
        html body .burger-art { height:440px; margin:20px 28px 160px; min-height:0; }
        html body .burger-thumbs { bottom:30px; left:28px; max-width:calc(100% - 56px); }
    }

    /* Editorial restaurant hero */
    body.front-only { background: #fff !important; padding: 0; }
    html body .burger-front { min-height: 100vh; }
    html body .burger-panel {
        background: #fff url("{{ asset('food_img/mi-cusina-hero-food-edge.png') }}") center center / 100% 100% no-repeat !important;
        box-shadow: none;
        display: block;
        min-height: 100vh;
        overflow: hidden;
    }
    .burger-panel::before {
        background: #fff;
        border-bottom: 1px solid #f1f0ec;
        color: #565853;
        content: '☎  (032) 345 0107     ✉  hello@micusina.com';
        display: flex;
        align-items: center;
        font-size: 12px;
        height: 39px;
        letter-spacing: .01em;
        padding-left: clamp(22px, 9vw, 130px);
    }
    .burger-panel::before { content:none !important; display:none !important; }
    html body .burger-front.is-inner-page,
    html body .burger-front.is-inner-page .burger-panel { min-height:0 !important; }
    html body .burger-front.is-inner-page .burger-copy,
    html body .burger-front.is-inner-page .hero-food-frame,
    html body .burger-front.is-inner-page .burger-art,
    html body .burger-front.is-inner-page .burger-thumbs { display:none !important; }
    html body .burger-topbar {
        align-items: center;
        background: #fff !important;
        border-bottom: 1px solid #f1f0ec;
        display: grid;
        grid-template-columns: 220px 1fr 220px;
        height: 91px;
        left: 0;
        padding: 0 clamp(22px, 9vw, 130px);
        position: relative;
        right: auto;
        top: 0;
        z-index: 1000;
    }
    html body .burger-mark { align-items: center; display: flex; gap: 9px; width: auto; }
    html body .burger-mark::after { content: 'Mi Cusina'; color: #292b27; font-family: Georgia, serif; font-size: 31px; font-style: italic; font-weight: 700; white-space: nowrap; }
    html body .burger-mark img { height: 46px; object-fit: contain; width: 54px; }
    html body .burger-nav { gap: clamp(17px, 2.6vw, 42px); }
    html body.front-only .burger-nav a { color: #2d302c !important; font-size: 14px; font-weight: 600; padding: 9px 13px; }
    html body.front-only .burger-nav a.active { background: #e3e8d9; border-radius: 999px; }
    html body .burger-login { justify-content: flex-end; }
    html body.front-only .burger-login a, html body.front-only .burger-login button { color: #2d302c !important; font-size: 13px; }
    html body .mobile-app-button {
        background: #fff !important;
        border: 1px solid #272925 !important;
        border-radius: 999px;
        box-shadow: none;
        color: #272925 !important;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        padding: 12px 18px;
        white-space: nowrap;
    }
    html body .mobile-app-button:hover { background: #272925 !important; color: #fff !important; }
    html body .homepage-download { margin-top:16px; text-decoration:none; }
    html body .burger-login > a:first-child { border: 1px solid #272925; border-radius: 999px; font-weight: 700; padding: 12px 19px; }
    html body .burger-login > a.track-order-link:first-child { border: 0 !important; border-radius: 0 !important; font-weight: 600; padding: 9px 0 !important; }
    html body .burger-login > a:last-child { border: 1px solid #272925; border-radius: 999px; font-weight: 700; padding: 12px 19px; }
    html body .burger-copy {
        align-items: center;
        background: transparent !important;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: calc(100vh - 91px);
        padding: 70px 24px 122px;
        text-align: center;
        z-index: 5;
    }
    html body .burger-copy h1, html body .burger-copy h1 span {
        color: #292b27 !important;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: clamp(55px, 5vw, 88px);
        font-weight: 400;
        letter-spacing: -.055em;
        line-height: .93;
        margin-bottom: 23px;
        text-shadow: none;
        text-transform: none;
    }
    html body.front-only .burger-copy h1,
    html body.front-only .burger-copy h1 span { color: #111 !important; }
    html body .burger-copy p { color: #4e504c !important; font-size: 17px; line-height: 1.6; max-width: 470px; }
    html body .burger-actions { gap: 14px; }
    html body .burger-primary, html body .burger-secondary { border-radius: 999px; font-size: 14px; min-height: 50px; padding: 0 27px; }
    html body.front-only .burger-primary {
        background: linear-gradient(135deg, #ff6b72 0%, #ed0da8 100%) !important;
        border: 0 !important;
        color: #fff !important;
        min-width: auto;
    }
    html body.front-only .burger-secondary { background: linear-gradient(135deg, #b678aa 0%, #8f5f87 100%); border: 0; color: #fff !important; }
    html body.front-only .burger-secondary:hover { background: linear-gradient(135deg, #9f668f 0%, #74496d 100%); border-color: transparent; color: #fff !important; }
    html body.front-only .burger-login .guest-auth-link { background: #fff; border: 1px solid #9b6b92; color: #9b6b92 !important; }
    html body.front-only .burger-login .guest-register-link { background: #fff; color: #9b6b92 !important; }
    html body .burger-art { display: none; }
    html body .burger-art img { filter: drop-shadow(0 20px 15px rgba(0,0,0,.14)); height: 100%; object-fit: contain; object-position: right center; width: 100%; }
    html body .burger-thumbs { display: none; }
    .hero-food-frame {
        display: none !important;
        filter: drop-shadow(0 16px 15px rgba(42, 35, 23, .16));
        pointer-events: none;
        position: fixed !important;
        width: clamp(170px, 19vw, 330px);
        z-index: 4 !important;
    }
    .hero-food-frame img {
        display: block !important;
        height: auto;
        max-width: none;
        mix-blend-mode: screen;
        visibility: visible !important;
        width: 100%;
    }
    .hero-food-frame--top-left { left: clamp(-45px, 1vw, 24px); top: 135px; transform: rotate(-8deg); }
    .hero-food-frame--top-right { right: clamp(-55px, 1vw, 24px); top: 135px; transform: rotate(7deg); }
    .hero-food-frame--bottom-left { bottom: clamp(-46px, -1vw, -12px); left: clamp(-35px, 2vw, 55px); transform: rotate(7deg); }
    .hero-food-frame--bottom-right { bottom: clamp(-48px, -1vw, -10px); right: clamp(-45px, 1vw, 45px); transform: rotate(-7deg); }
    html body .burger-thumb, html body .burger-thumb.active { background: transparent !important; border: 0; border-radius: 0; box-shadow: none; flex-basis: 82px; height: 82px; width: 82px; }
    html body .burger-thumb:not(.active) { display: none; }
    html body .burger-thumb img { object-fit: contain; }

    @media (max-width: 991.98px) {
        body.front-only { height: auto; min-height: 100vh; overflow-y: auto; padding: 0; }
        html body .burger-front, html body .burger-panel { min-height: 100vh; }
        html body .burger-panel { display: flex; flex-direction: column; }
        html body .burger-topbar {
            column-gap: 12px;
            flex-shrink: 0;
            grid-template-columns: minmax(0, 1fr) auto;
            height: auto;
            min-height: 76px;
            padding: 12px 20px;
            row-gap: 10px;
        }
        html body .burger-mark { grid-column: 1; grid-row: 1; }
        html body .burger-login { grid-column: 2; grid-row: 1; }
        html body .burger-mark::after { font-size: 24px; }
        html body .burger-mark img { height: 36px; width: 42px; }
        html body .burger-nav {
            border-top: 1px solid #f1f0ec;
            display: grid;
            gap: 6px;
            grid-column: 1 / -1;
            grid-row: 2;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            padding-top: 10px;
        }
        html body.front-only .burger-nav a {
            align-items: center;
            border-radius: 12px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            line-height: 1.25;
            min-height: 44px;
            padding: 8px;
            text-align: center;
        }
        html body.front-only .burger-nav .burger-download { background: #8f5f87 !important; color: #fff !important; }
        html body.front-only .burger-nav .burger-download:hover { background: #74496d !important; }
        html body.front-only .burger-nav a:focus-visible { outline: 2px solid #74496d; outline-offset: 2px; }
        html body .burger-copy { flex: 1; min-height: 0; padding: 72px 22px 125px; }
        html body .burger-art { opacity: .24; right: -14vw; top: 42%; width: 78vw; }
        html body .burger-thumbs { bottom: 18px; left: 18px; }
        .hero-food-frame { opacity: .3; width: 170px; }
        .hero-food-frame--top-left { left: -76px; top: 130px; }
        .hero-food-frame--top-right { right: -76px; top: 145px; }
        .hero-food-frame--bottom-left { bottom: -48px; left: -57px; }
        .hero-food-frame--bottom-right { bottom: -50px; right: -59px; }
        .burger-panel::before { height: 32px; justify-content: center; padding: 0; }
    }
    /* Phone-first homepage layout: prevents the brand, auth buttons and headline from overflowing. */
    @media (max-width: 575.98px) {
        html body .burger-panel {
            background-position: center center !important;
            background-size: cover !important;
        }
        html body .burger-topbar {
            grid-template-columns: minmax(0, 1fr) auto;
            min-height: 72px;
            padding: 10px 14px;
        }
        html body .burger-mark { gap: 6px; min-width: 0; }
        html body .burger-mark::after {
            font-size: clamp(18px, 5.7vw, 23px);
            overflow: hidden;
            text-overflow: ellipsis;
        }
        html body .burger-mark img { height: 34px; width: 38px; }
        html body .burger-login { gap: 7px; }
        html body .burger-login > a:first-child,
        html body .burger-login > a:last-child {
            font-size: 12px;
            padding: 9px 11px;
        }
        html body .burger-copy {
            min-height: 0;
            padding: 46px 18px 76px;
        }
        html body.front-only .burger-nav a { font-size: 12px; }
        html body .burger-copy h1,
        html body .burger-copy h1 span {
            font-size: clamp(40px, 11.5vw, 48px);
            letter-spacing: -.045em;
            line-height: .98;
            margin-bottom: 18px;
        }
        html body .burger-copy p {
            font-size: 16px;
            line-height: 1.45;
            max-width: 330px;
        }
        html body .burger-actions { gap: 10px; width: min(100%, 350px); }
        html body .homepage-download { font-size:13px; padding:11px 16px; }
        html body .burger-primary,
        html body .burger-secondary {
            flex: 1 1 0;
            font-size: 13px;
            justify-content: center;
            min-height: 48px;
            padding: 0 12px;
        }
    }
    @media (max-width: 359.98px) {
        html body .burger-nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

@php($isHomepage = request()->getPathInfo() === '/')
<header class="burger-front {{ $isHomepage ? '' : 'is-inner-page' }}" id="home">
    <div class="burger-panel">
        @if($isHomepage)
            <img class="homepage-food-image" src="{{ asset('food_img/mi-cusina-vivid-pink-hero.png') }}" alt="Chicken and rice with egg, golden fries, and creamy dip on a pink table" fetchpriority="high">
        @endif
        <div class="burger-topbar">
            <a class="burger-mark" href="{{ url('/') }}" aria-label="Mi Cusina Home">
                <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina">
            </a>
            <nav class="burger-nav" aria-label="Primary">
                <a class="active" href="{{ url('/') }}">Home</a>
                <a href="{{ url('/?section=food') }}">Menu</a>
                <a href="{{ url('/?section=book') }}">Book Table</a>
                <a class="burger-download" href="{{ route('mobile-app.install') }}">Download App</a>
            </nav>
            <div class="burger-login">
                @if (Route::has('login'))
                    @auth
                        <a class="track-order-link track-action" href="{{ url('my_orders') }}" data-tooltip="Track Order" title="Track Order" aria-label="Track Order"><i class="ti-truck" aria-hidden="true"></i><span class="track-action-label">Track Order</span></a>
                        <a class="track-order-link track-action" href="{{ route('bookings.index') }}" data-tooltip="Track Booking" title="Track Booking" aria-label="Track Booking"><i class="ti-calendar" aria-hidden="true"></i><span class="track-action-label">Track Booking</span></a>
                        <details class="front-user-menu">
                            <summary aria-label="Open user menu">@if(Auth::user()->profile_photo_path)<img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">@else{{ $userInitial }}@endif</summary>
                            <div class="front-user-dropdown">
                                <div class="front-user-info">
                                    <strong>{{ Auth::user()->name }}</strong>
                                    <span>{{ Auth::user()->email }}</span>
                                </div>
                                <form action="{{ route('customer.profile-photo.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label class="customer-photo-label" for="customerProfilePhotoHome">Upload Profile Picture</label>
                                    <input id="customerProfilePhotoHome" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden onchange="if(this.files.length)this.form.submit()">
                                    @error('photo')<small class="customer-photo-error">{{ $message }}</small>@enderror
                                </form>
                                <a class="profile-action" href="{{ route('profile.show') }}">Profile</a>
                                <form action="{{ route('customer.logout') }}" method="POST" data-logout-form>
                                    @csrf
                                    <button class="logout-action" type="button" data-logout-trigger>Log Out</button>
                                </form>
                            </div>
                        </details>
                    @else
                        <a class="guest-auth-link guest-login-link" href="{{ route('login') }}">Log In</a>
                        <a class="guest-auth-link guest-register-link" href="{{ Route::has('register') ? route('register') : url('/') }}">Register</a>
                    @endauth
                @endif
            </div>
        </div>

        <div class="burger-copy">
            <h1>Comfort food<br><span class="headline-accent">for your table</span></h1>
            <p>Discover Filipino comfort food, thoughtfully made and served with love at Mi Cusina.</p>
            <div class="burger-actions">
                <a class="burger-primary" href="{{ url('/?section=food') }}"><i class="ti-view-list-alt" aria-hidden="true"></i> Order Now <span aria-hidden="true">→</span></a>
                <a class="burger-secondary" href="{{ url('/?section=about') }}"><i class="ti-control-play" aria-hidden="true"></i> About Us</a>
            </div>
        </div>

        <div class="hero-food-frame hero-food-frame--top-left" aria-hidden="true">
            <img src="{{ asset('food_img/hero-hotdog-sandwich-transparent.png') }}" alt="">
        </div>
        <div class="hero-food-frame hero-food-frame--top-right" aria-hidden="true">
            <img src="{{ asset('food_img/hero-chicken-teriyaki-transparent.png') }}" alt="">
        </div>
        <div class="hero-food-frame hero-food-frame--bottom-left" aria-hidden="true">
            <img src="{{ asset('food_img/hero-adobo-bunwich-transparent.png') }}" alt="">
        </div>
        <div class="hero-food-frame hero-food-frame--bottom-right" aria-hidden="true">
            <img src="{{ asset('food_img/hero-burger-spaghetti-transparent.png') }}" alt="">
        </div>

        <div class="burger-art" aria-live="polite">
            <img id="burgerMainImage" src="{{ asset('food_img/hero-chicken-teriyaki-transparent.png') }}" alt="Featured chicken burger">
        </div>

        <div class="burger-thumbs" aria-label="Featured burgers">
            <button class="burger-thumb active" type="button" data-image="{{ asset('food_img/hero-chicken-teriyaki-transparent.png') }}" aria-label="Show chicken burger">
                <img src="{{ asset('food_img/hero-chicken-teriyaki-transparent.png') }}" alt="">
            </button>
            <button class="burger-thumb" type="button" data-image="{{ asset('food_img/hero-adobo-bunwich-transparent.png') }}" aria-label="Show adobo bunwich">
                <img src="{{ asset('food_img/hero-adobo-bunwich-transparent.png') }}" alt="">
            </button>
            <button class="burger-thumb" type="button" data-image="{{ asset('food_img/hero-burger-spaghetti-transparent.png') }}" aria-label="Show hotdog sandwich">
                <img src="{{ asset('food_img/hero-burger-spaghetti-transparent.png') }}" alt="">
            </button>
            <button class="burger-thumb" type="button" data-image="{{ asset('food_img/hero-chicken-burger-transparent.png') }}" aria-label="Show teriyaki rice bowl">
                <img src="{{ asset('food_img/hero-chicken-burger-transparent.png') }}" alt="">
            </button>
            <button class="burger-thumb" type="button" data-image="{{ asset('food_img/hero-hotdog-sandwich-transparent.png') }}" aria-label="Show burger spaghetti">
                <img src="{{ asset('food_img/hero-hotdog-sandwich-transparent.png') }}" alt="">
            </button>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mainImage = document.getElementById('burgerMainImage');
        var thumbs = document.querySelectorAll('.burger-thumb');

        function activateThumb(thumb) {
            if (!thumb || !thumb.dataset.image) {
                return;
            }

            if (mainImage.src !== thumb.dataset.image) {
                mainImage.src = thumb.dataset.image;
            }

            thumbs.forEach(function (item) {
                item.classList.remove('active');
            });

            thumb.classList.add('active');
        }

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('mouseenter', function () {
                activateThumb(thumb);
            });

            thumb.addEventListener('focus', function () {
                activateThumb(thumb);
            });

            thumb.addEventListener('click', function () {
                activateThumb(thumb);
            });
        });
    });
</script>
@else
<style>
    body.content-page {
        background: #000;
    }

    .inner-navbar {
        background: #fff !important;
        border-bottom: 1px solid #f1f0ec;
        min-height: 91px;
        padding: 0 clamp(22px, 9vw, 130px);
        z-index: 1000;
    }

    .inner-navbar.affix {
        background: #fff !important;
        border: 0;
        min-height: 91px;
    }

    .inner-navbar .navbar-collapse {
        align-items: center;
        display: grid !important;
        grid-template-columns: 220px 1fr 220px;
    }

    .inner-brand {
        align-items: center;
        color: #292b27 !important;
        display: flex;
        font-family: Georgia, serif;
        font-size: 31px;
        font-style: italic;
        font-weight: 700;
        gap: 9px;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .inner-brand img { height: 46px; object-fit: contain; width: 54px; }

    .inner-navbar .navbar-nav {
        align-items: center;
        display: flex;
        gap: clamp(17px, 2.6vw, 42px);
        margin: 0;
        width: auto !important;
    }

    .inner-navbar .navbar-nav:first-of-type { justify-content: center; }
    .inner-navbar .navbar-nav:last-of-type { gap: 16px; justify-content: flex-end; }

    .inner-navbar .nav-link {
        background: transparent;
        border: 0;
        color: #000 !important;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        padding: 0 !important;
    }

    .inner-navbar .nav-link:hover,
    .inner-navbar .nav-link.active {
        color: #000 !important;
    }

    .inner-navbar .mobile-app-button { margin: 0 3px; }

    .inner-navbar .cart-badge-link {
        align-items: center;
        display: inline-flex;
        gap: 7px;
    }

    .inner-navbar .cart-badge {
        align-items: center;
        background: #F88379;
        border-radius: 999px;
        color: #000;
        display: inline-flex;
        font-size: 12px;
        font-weight: 900;
        height: 20px;
        justify-content: center;
        line-height: 1;
        min-width: 20px;
        padding: 0 6px;
    }

    .inner-navbar .front-user-menu {
        position: relative;
        z-index: 1001;
    }

    .inner-navbar .front-user-menu summary {
        align-items: center;
        background: #eef3ff;
        border: 3px solid #fff;
        border-radius: 999px;
        box-shadow: 0 0 0 2px #1d2330;
        color: #000;
        cursor: pointer;
        display: inline-flex;
        font-size: 18px;
        font-weight: 800;
        height: 46px;
        justify-content: center;
        list-style: none;
        width: 46px;
    }

    .inner-navbar .front-user-menu summary::-webkit-details-marker {
        display: none;
    }

    .inner-navbar .front-user-menu summary img {
        border-radius: 999px;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .inner-navbar .front-user-dropdown {
        background: #111;
        border: 1px solid #2b2b2b;
        border-radius: 8px;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .34);
        color: #000;
        min-width: 230px;
        padding: 10px;
        position: absolute;
        right: 0;
        top: calc(100% + 12px);
        z-index: 1002;
        pointer-events: auto;
    }

    .inner-navbar .front-user-info {
        border-bottom: 1px solid #2b2b2b;
        margin-bottom: 8px;
        padding: 8px 10px 12px;
    }

    .inner-navbar .front-user-info strong,
    .inner-navbar .front-user-info span {
        display: block;
    }

    .inner-navbar .front-user-info strong {
        color: #000;
        font-size: 15px;
    }

    .inner-navbar .front-user-info span {
        color: #000;
        font-size: 13px;
        margin-top: 4px;
    }

    .inner-navbar .front-user-dropdown form {
        margin: 0;
        width: 100%;
    }

    .inner-navbar .front-user-dropdown button {
        background: transparent !important;
        border: 0 !important;
        border-radius: 6px;
        box-sizing: border-box;
        color: #000 !important;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        padding: 10px;
        text-align: left;
        width: 100%;
    }

    .inner-navbar .front-user-dropdown .profile-action,
    .inner-navbar .front-user-dropdown .home-action,
    .inner-navbar .front-user-dropdown button.logout-action {
        background: #fff7f7 !important;
        border: 2px solid #ff746c !important;
        border-radius: 8px !important;
        box-sizing: border-box;
        box-shadow: inset 0 0 0 1px #ff746c;
        display: block;
        min-height: 48px;
    }

    .inner-navbar .front-user-dropdown .profile-action,
    .inner-navbar .front-user-dropdown .home-action { color: #000 !important; margin-bottom: 8px; }

    .inner-navbar .front-user-dropdown a {
        background: rgba(155, 107, 146, .08);
        border: 2px solid #9b6b92;
        border-radius: 6px;
        box-sizing: border-box;
        color: #000;
        display: block;
        font-size: 14px;
        font-weight: 800;
        min-height: 48px;
        padding: 10px;
        text-decoration: none;
    }

    .inner-navbar .front-user-dropdown .profile-action:hover,
    .inner-navbar .front-user-dropdown .home-action:hover,
    .inner-navbar .front-user-dropdown button.logout-action:hover { background: #ffecec !important; color: #000; }

    .inner-navbar .front-user-dropdown a:not(.profile-action):not(.home-action):hover,
    .inner-navbar .front-user-dropdown button:not(.logout-action):hover { background: #F88379; color: #000; }

    .inner-navbar .customer-photo-label {
        border-radius: 6px;
        color: #000;
        cursor: pointer;
        display: block;
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        padding: 10px;
    }

    .inner-navbar .customer-photo-label:hover { background: #F88379; color: #000; }
    .inner-navbar .customer-photo-error { color: #000; display: block; font-size: 11px; padding: 4px 10px; }

    .inner-navbar .navbar-toggler {
        margin-left: auto;
    }

    @media (max-width: 991.98px) {
        .inner-navbar {
            min-height: auto;
            padding: 16px 20px;
        }

        .inner-navbar .navbar-collapse.show {
            align-items: flex-start;
            display: flex !important;
            flex-direction: column;
            gap: 18px;
            margin-top: 16px;
        }

        .inner-navbar .navbar-collapse:not(.show) { display: none !important; }

        .inner-navbar .navbar-nav {
            align-items: flex-start;
            flex-direction: column;
            gap: 14px;
        }

        .inner-brand { display: none; }
    }
</style>
<nav class="custom-navbar inner-navbar navbar navbar-expand-lg navbar-dark fixed-top" data-spy="affix" data-offset-top="10">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <a class="inner-brand" href="{{ url('/') }}" aria-label="Mi Cusina Home">
            <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina">
            <span>Mi Cusina</span>
        </a>
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link {{ request('section') ? '' : 'active' }}" href="{{ url('/') }}">Home</a></li>
            <li class="nav-item"><a class="nav-link {{ request('section') === 'food' ? 'active' : '' }}" href="{{ url('/?section=food') }}">Menu</a></li>
            <li class="nav-item"><a class="nav-link {{ request('section') === 'about' ? 'active' : '' }}" href="{{ url('/?section=about') }}">About</a></li>
            <li class="nav-item"><a class="nav-link {{ request('section') === 'book' ? 'active' : '' }}" href="{{ url('/?section=book') }}">Book Table</a></li>
        </ul>
        <ul class="navbar-nav">
            @auth
                <li class="nav-item"><a class="nav-link track-order-link track-action" href="{{ url('my_orders') }}" data-tooltip="Track Order" title="Track Order" aria-label="Track Order"><i class="ti-truck" aria-hidden="true"></i><span class="track-action-label">Track Order</span></a></li>
                <li class="nav-item"><a class="nav-link track-order-link track-action" href="{{ route('bookings.index') }}" data-tooltip="Track Booking" title="Track Booking" aria-label="Track Booking"><i class="ti-calendar" aria-hidden="true"></i><span class="track-action-label">Track Booking</span></a></li>
            @endauth
            @if (Route::has('login'))
                @auth
                    <li class="nav-item">
                        <details class="front-user-menu">
                            <summary aria-label="Open user menu">@if(Auth::user()->profile_photo_path)<img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">@else{{ $userInitial }}@endif</summary>
                            <div class="front-user-dropdown">
                                <div class="front-user-info">
                                    <strong>{{ Auth::user()->name }}</strong>
                                    <span>{{ Auth::user()->email }}</span>
                                </div>
                                <form action="{{ route('customer.profile-photo.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label class="customer-photo-label" for="customerProfilePhotoInner">Upload Profile Picture</label>
                                    <input id="customerProfilePhotoInner" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden onchange="if(this.files.length)this.form.submit()">
                                    @error('photo')<small class="customer-photo-error">{{ $message }}</small>@enderror
                                </form>
                                <a class="profile-action" href="{{ route('profile.show') }}">Profile</a>
                                <form action="{{ route('customer.logout') }}" method="POST" data-logout-form>
                                    @csrf
                                    <button class="logout-action" type="button" data-logout-trigger>Log Out</button>
                                </form>
                            </div>
                        </details>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log In</a></li>
                @endauth
            @endif
        </ul>
    </div>
</nav>
@endif

@if(request('section') && request()->path() === '/')
<button class="floating-cart-button" type="button" data-cart-popup data-cart-url="{{ url('my_cart') }}?embed=1" aria-label="Open cart">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M3 4h2l2.1 10.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L20 9H7"></path>
        <circle cx="10" cy="20" r="1.35"></circle>
        <circle cx="17" cy="20" r="1.35"></circle>
    </svg>
    <span class="floating-cart-count">{{ $cartBadgeCount }}</span>
</button>

<div class="cart-popup" id="cartPopup" aria-hidden="true">
    <div class="cart-popup-backdrop" data-close-cart-popup></div>
    <section class="cart-popup-panel" role="dialog" aria-label="Shopping cart">
        <header class="cart-popup-header">
            <strong>Shopping Cart</strong>
            <button class="cart-popup-close" type="button" data-close-cart-popup aria-label="Close cart">&times;</button>
        </header>
        <iframe class="cart-popup-frame" name="cartPopupFrame" title="Shopping cart"></iframe>
    </section>
</div>
@endif

<style>
    html body .floating-cart-button {
        align-items: center;
        background: #fff !important;
        border: 2px solid #c46cff !important;
        border-radius: 50%;
        bottom: 24px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .24);
        display: flex;
        height: 64px;
        justify-content: center;
        position: fixed;
        right: 24px;
        text-decoration: none !important;
        transition: transform .2s ease, background .2s ease, border-color .2s ease;
        width: 64px;
        z-index: 1300;
    }

    html body .floating-cart-button:hover {
        background: #fdf7ff !important;
        border-color: #a93ee9 !important;
        transform: translateY(-3px);
    }

    html body .floating-cart-button svg {
        fill: none !important;
        height: 31px;
        stroke: #000 !important;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 2.25;
        width: 31px;
    }

    html body .floating-cart-count {
        align-items: center;
        background: #e11d48 !important;
        border: 2px solid #fff;
        border-radius: 999px;
        color: #fff !important;
        display: flex;
        font-size: 12px;
        font-weight: 800;
        height: 23px;
        justify-content: center;
        min-width: 23px;
        padding: 0 5px;
        position: absolute;
        right: -7px;
        top: -7px;
    }

    .cart-popup {
        display: none;
        position: fixed;
        right: 24px;
        bottom: 104px;
        z-index: 3001;
    }

    .cart-popup.is-open { display: block; }
    .cart-popup-backdrop { display: none; }
    .cart-popup-panel { background: #fff; border: 1px solid rgba(0, 0, 0, .08); border-radius: 8px; box-shadow: 0 18px 60px rgba(0, 0, 0, .28); display: flex; flex-direction: column; height: min(500px, calc(100vh - 128px)); overflow: hidden; transform: translateY(8px); transition: transform .2s ease, opacity .2s ease; width: min(380px, calc(100vw - 32px)); }
    .cart-popup.is-open .cart-popup-panel { transform: translateY(0); }
    .cart-popup-header { align-items: center; background: #242424; color: #fff; display: flex; flex: 0 0 auto; justify-content: space-between; padding: 14px 16px; }
    .cart-popup-header strong { font-size: 16px; }
    .cart-popup-frame { border: 0; flex: 1 1 auto; min-height: 0; width: 100%; }
    .cart-popup-close { background: transparent; border: 0; color: #fff; cursor: pointer; font-size: 28px; line-height: 1; padding: 0; }

    @media (max-width:640px) {
        .floating-cart-button {
            bottom: 16px;
            height: 56px;
            right: 16px;
            width: 56px;
        }

        .cart-popup { bottom: 88px; right: 16px; }
        .cart-popup-panel { height: min(500px, calc(100vh - 104px)); width: min(calc(100vw - 32px), 380px); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var button = document.querySelector('[data-cart-popup]');
        var popup = document.getElementById('cartPopup');
        var frame = popup ? popup.querySelector('.cart-popup-frame') : null;

        if (!button || !popup || !frame) return;

        function closeCartPopup() {
            popup.classList.remove('is-open');
            popup.setAttribute('aria-hidden', 'true');
            button.focus();
        }

        button.addEventListener('click', function () {
            openCartPopup();
        });

        function openCartPopup() {
            // Reload the embedded cart every time so newly added items and
            // quantities are always shown while the menu stays behind it.
            frame.src = button.dataset.cartUrl;
            popup.classList.add('is-open');
            popup.setAttribute('aria-hidden', 'false');
            popup.querySelector('.cart-popup-close').focus();
        }

        // Menu actions use this event after a successful AJAX add, keeping
        // the customer on the current page while showing the live cart.
        document.addEventListener('cart:updated', function (event) {
            var count = event.detail && event.detail.count;
            document.querySelectorAll('.floating-cart-count').forEach(function (badge) {
                if (typeof count !== 'undefined') badge.textContent = count;
            });
            openCartPopup();
        });

        popup.addEventListener('click', function (event) {
            if (!event.target.closest('[data-close-cart-popup]')) return;
            closeCartPopup();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && popup.classList.contains('is-open')) {
                closeCartPopup();
            }
        });
    });
</script>

@if(session()->has('message'))
    <div class="site-flash" id="siteFlash">{{ session()->get('message') }}</div>
    <script>
        window.setTimeout(function () {
            var flash = document.getElementById('siteFlash');
            if (flash) {
                flash.remove();
            }
        }, 2000);
    </script>
@endif

@auth
    @include('components.logout-confirmation')
@endauth
