<x-guest-layout>
    <style>
        body {
            margin: 0;
            overflow-x: hidden;
        }

        .auth-scene {
            --auth-orange: #fcb13f;
            --auth-orange-dark: #d48713;
            --auth-green: #86c83d;
            --auth-cream: #fffaf2;
            background: var(--auth-orange);
            color: #111;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .auth-scene::before {
            background:
                linear-gradient(rgba(252, 177, 63, .72), rgba(252, 177, 63, .72)),
                radial-gradient(circle at 7% 30%, transparent 0 48px, rgba(184, 111, 0, .5) 50px 54px, transparent 56px),
                radial-gradient(circle at 24% 82%, transparent 0 36px, rgba(184, 111, 0, .45) 38px 42px, transparent 44px),
                radial-gradient(circle at 38% 48%, transparent 0 54px, rgba(184, 111, 0, .42) 56px 60px, transparent 62px);
            content: "";
            inset: 0 auto 0 0;
            position: absolute;
            width: 33%;
            z-index: 0;
        }

        .auth-scene::after {
            background: var(--auth-cream);
            border-top-left-radius: 84% 48%;
            content: "";
            inset: 8% -10% -14% 32.8%;
            position: absolute;
            transform: rotate(-7deg);
            transform-origin: top left;
            z-index: 0;
        }

        .auth-logo {
            left: 18.8%;
            position: absolute;
            top: 18px;
            z-index: 4;
        }

        .auth-logo img {
            height: 74px;
            object-fit: contain;
            width: 150px;
        }

        .auth-pattern {
            border: 5px solid rgba(184, 111, 0, .72);
            border-radius: 48% 52% 44% 56%;
            height: 760px;
            left: -170px;
            opacity: .75;
            position: absolute;
            top: 168px;
            width: 920px;
            z-index: 0;
        }

        .auth-pattern::before,
        .auth-pattern::after {
            border: 5px solid rgba(184, 111, 0, .65);
            border-radius: 54% 46% 60% 40%;
            content: "";
            position: absolute;
        }

        .auth-pattern::before {
            inset: 46px 70px 80px 90px;
            transform: rotate(18deg);
        }

        .auth-pattern::after {
            inset: 116px 128px 62px 146px;
            transform: rotate(-22deg);
        }

        .auth-zigzag {
            border-bottom: 6px solid #111;
            border-left: 6px solid #111;
            height: 28px;
            position: absolute;
            transform: skew(-26deg) rotate(-14deg);
            width: 58px;
            z-index: 3;
        }

        .auth-zigzag.one { left: 5%; top: 48px; }
        .auth-zigzag.two { left: 64%; top: 40px; }
        .auth-zigzag.three { bottom: 58px; left: 54%; }
        .auth-zigzag.four {
            border-color: var(--auth-orange);
            bottom: 45%;
            right: 4%;
        }

        .auth-faded-leaf {
            border: 8px solid rgba(0, 0, 0, .08);
            border-radius: 82% 18% 72% 28%;
            height: 250px;
            position: absolute;
            right: -62px;
            top: -10px;
            transform: rotate(-18deg);
            width: 165px;
            z-index: 2;
        }

        .auth-faded-leaf.bottom {
            bottom: -64px;
            height: 260px;
            opacity: .8;
            right: 40px;
            top: auto;
            transform: rotate(32deg);
            width: 185px;
        }

        .auth-faded-leaf::before,
        .auth-faded-leaf::after {
            background: rgba(0, 0, 0, .07);
            content: "";
            position: absolute;
        }

        .auth-faded-leaf::before {
            height: 100%;
            left: 50%;
            top: 0;
            transform: rotate(18deg);
            width: 7px;
        }

        .auth-faded-leaf::after {
            border: solid rgba(0, 0, 0, .07);
            border-width: 6px 0 0 6px;
            height: 110px;
            left: 50px;
            top: 68px;
            transform: rotate(28deg);
            width: 105px;
        }

        .auth-content {
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }

        .auth-food {
            align-self: stretch;
            bottom: 0;
            left: 12%;
            min-height: 100vh;
            overflow: hidden;
            position: absolute;
            top: 0;
            width: 21%;
        }

        .auth-food::after {
            background: linear-gradient(180deg, rgba(0, 0, 0, .16), rgba(0, 0, 0, .7));
            content: "";
            inset: 0;
            position: absolute;
        }

        .auth-food img {
            height: 100%;
            object-fit: cover;
            object-position: center;
            width: 100%;
        }

        .auth-card {
            margin: 0 0 0 58.4%;
            max-width: 435px;
            padding: 29vh 0 0;
            position: relative;
            width: min(100%, 435px);
            z-index: 3;
        }

        .auth-card h1 {
            color: #000;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 0;
            margin: 0 0 72px;
            text-align: center;
            text-transform: uppercase;
        }

        .auth-field {
            margin-bottom: 34px;
        }

        .auth-field input {
            background: #e6e6e6;
            border: 0;
            border-radius: 24px;
            color: #222;
            font-size: 18px;
            font-weight: 700;
            height: 65px;
            padding: 0 36px;
            width: 100%;
        }

        .auth-field input::placeholder {
            color: #707070;
        }

        .auth-helper {
            color: var(--auth-green) !important;
            display: block;
            font-size: 18px;
            font-weight: 700;
            margin: -12px 0 22px;
            text-align: right;
        }

        .auth-submit {
            background: var(--auth-green) !important;
            border: 0 !important;
            border-radius: 8px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, .18);
            color: #000 !important;
            display: block;
            font-size: 18px;
            font-weight: 900;
            height: 54px;
            margin: 0 auto 34px;
            width: 140px;
        }

        .auth-divider {
            align-items: center;
            color: var(--auth-green);
            display: grid;
            font-size: 18px;
            font-weight: 700;
            gap: 14px;
            grid-template-columns: 1fr auto 1fr;
            margin-bottom: 56px;
        }

        .auth-divider::before,
        .auth-divider::after {
            background: var(--auth-green);
            content: "";
            height: 1px;
        }

        .auth-socials {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(2, 1fr);
            margin-bottom: 44px;
        }

        .auth-social {
            align-items: center;
            background: transparent;
            border: 1px solid var(--auth-green);
            border-radius: 28px;
            color: #000;
            display: flex;
            font-size: 18px;
            font-weight: 900;
            gap: 28px;
            height: 60px;
            justify-content: center;
        }

        .auth-social svg {
            height: 30px;
            width: 30px;
        }

        .auth-switch {
            color: #727272;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }

        .auth-switch a {
            color: var(--auth-green) !important;
            font-weight: 900;
        }

        .auth-errors {
            background: rgba(255, 255, 255, .82);
            border: 1px solid #f25f5c;
            border-radius: 10px;
            color: #9f2220;
            font-weight: 700;
            margin-bottom: 18px;
            padding: 12px 16px;
        }

        @media (max-width: 920px) {
            body { overflow: auto; }
            .auth-food,
            .auth-pattern {
                display: none;
            }

            .auth-scene::after {
                inset: 16% -18% -8% -10%;
            }

            .auth-content {
                padding: 130px 18px 36px;
            }

            .auth-logo {
                left: 24px;
            }

            .auth-card h1 {
                font-size: 32px;
                margin-bottom: 42px;
            }

            .auth-card {
                margin: 0 auto;
                padding: 0;
            }

            .auth-socials {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <style>
        /* Mi Cusina food-framed authentication layout */
        html, body { background: #f7f6f7 url("{{ asset('assets/imgs/auth-three-bowls.png') }}") center / cover fixed no-repeat !important; }
        html body .font-sans, html body .auth-scene { background: transparent !important; }
        .auth-scene {
            background: transparent !important;
            min-height: 100vh;
        }
        .auth-scene::before { content: 'Login'; background: none; color: rgba(28, 27, 29, .08); font-family: Georgia, serif; font-size: clamp(90px, 13vw, 210px); font-weight: 700; inset: 12px 0 auto; line-height: .8; text-align: center; width: 100%; z-index: 0; }
        .auth-scene::after, .auth-pattern, .auth-zigzag, .auth-faded-leaf, .auth-food { display: none !important; }
        .auth-bowl-art { display: block !important; height: 100vh; inset: 0; object-fit: cover; pointer-events: none; position: fixed; visibility: visible !important; width: 100vw; z-index: 2001; }
        .auth-bowl-art { display: none !important; }
        .auth-bowl-piece { display: block !important; height: 280px; overflow: hidden; pointer-events: none; position: fixed; width: 280px; z-index: 2001; }
        .auth-bowl-piece img { display: block !important; max-width: none !important; position: absolute; visibility: visible !important; width: 1000px; }
        .auth-bowl-piece.left { left: 0; top: 35%; }
        .auth-bowl-piece.left img { left: 0; top: -175px; }
        .auth-bowl-piece.top-right { right: 0; top: 0; }
        .auth-bowl-piece.top-right img { left: -790px; top: 0; }
        .auth-bowl-piece.bottom-right { bottom: 0; right: 0; }
        .auth-bowl-piece.bottom-right img { left: -790px; top: -360px; }
        .auth-content .auth-bowl-piece { position: absolute; z-index: 1; }
        .auth-content .auth-bowl-piece { display: none !important; }
        .auth-content .auth-bowl-art { display: block !important; height: 100%; inset: 0; max-width: none !important; object-fit: cover; position: absolute !important; visibility: visible !important; width: 100%; z-index: 1; }
        .auth-content { background: #f2f1f3; min-height: 100vh; padding: 90px 22px 50px; }
        .auth-content .auth-bowl-art { object-fit: fill; }
        .auth-card { max-width: 300px; padding: 27px 30px; }
        .auth-card h1 { font-size: 23px; margin-bottom: 20px; }
        .auth-field input { height: 40px; font-size: 12px; }
        .auth-submit { height: 38px; font-size: 12px; }
        html body .auth-scene .auth-submit { background: #ed0da8 !important; color: #fff !important; }
        .auth-social { height: 36px; font-size: 11px; }
        .auth-social svg { height: 16px; width: 16px; }
        .auth-switch { font-size: 11px; }
        .auth-content .auth-bowl-piece { border-radius: 50%; box-shadow: 0 16px 30px rgba(30, 28, 30, .16); }
        .auth-content .auth-bowl-piece.left { height: 250px; top: 37%; width: 250px; }
        .auth-content .auth-bowl-piece.top-right { height: 215px; right: 4%; top: 2%; width: 215px; }
        .auth-content .auth-bowl-piece.bottom-right { bottom: 5%; height: 230px; right: 4%; width: 230px; }
        .auth-content { z-index: 2002 !important; }
        .auth-logo { z-index: 2003 !important; }
        .auth-logo { align-items: center; display: flex; gap: 10px; left: clamp(22px, 8vw, 150px); top: 20px; }
        .auth-logo { background: transparent !important; border: 2px solid #f2a1eb; border-radius: 50%; box-shadow: 0 0 7px rgba(242, 161, 235, .7); height: 64px; overflow: hidden; width: 64px; }
        .auth-logo::after { display: none; }
        .auth-logo img { height: 64px; max-width: none; mix-blend-mode: normal; transform: scale(1.35); width: 64px; }
        .auth-content { align-items: center; display: flex; justify-content: center; min-height: 100vh; padding: 110px 22px 42px; }
        .auth-card { background: rgba(255,255,255,.94); box-shadow: 0 18px 45px rgba(37, 33, 38, .16); margin: 0; max-width: 360px; padding: 34px 38px; width: 100%; }
        .auth-card h1 { color: #151515; font-family: Georgia, serif; font-size: 30px; font-weight: 700; margin: 0 0 26px; text-transform: none; }
        .auth-field { margin-bottom: 14px; }
        .auth-field input { background: #f5f4f5; border: 1px solid #e6e2e5; border-radius: 5px; box-sizing: border-box; font-size: 14px; font-weight: 500; height: 46px; padding: 0 15px; }
        .auth-helper { color: #4f4c50 !important; font-size: 12px; margin: -3px 0 17px; }
        .auth-submit { background: linear-gradient(135deg, #ff6b72, #ed0da8) !important; border-radius: 999px; box-shadow: 0 12px 25px rgba(237, 13, 168, .25); color: #fff !important; font-size: 14px; height: 45px; margin: 0 auto 19px; width: 100%; }
        .auth-divider { color: #999; font-size: 12px; margin-bottom: 18px; }
        .auth-divider::before, .auth-divider::after { background: #e4e1e4; }
        .auth-socials { gap: 10px; grid-template-columns: repeat(2, 42px); justify-content: center; margin-bottom: 20px; }
        .auth-social { border-color: #e4e1e4; border-radius: 5px; font-size: 12px; gap: 0; height: 42px; width: 42px; }
        .auth-social svg { height: 18px; width: 18px; }
        .auth-switch { font-size: 13px; }
        .auth-switch a { color: #ed0da8 !important; }
        .auth-content .auth-card { z-index: 5; }
        .auth-password-field { position: relative; }
        .auth-password-field input { padding-right: 56px; }
        .auth-field .auth-password-toggle {
            align-items: center;
            background: transparent !important;
            border: 0 !important;
            color: #666 !important;
            cursor: pointer;
            display: flex;
            height: 44px;
            justify-content: center;
            padding: 0;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
        }
        .auth-field .auth-password-toggle:hover { background: transparent !important; color: #222 !important; }
        .auth-password-toggle svg { height: 20px; width: 20px; }
        @media (max-width: 640px) { .auth-logo::after { font-size: 24px; } .auth-content { padding-top: 100px; } .auth-card { padding: 28px 24px; } }
    </style>
    <main class="auth-scene">
        <img class="auth-bowl-art" src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt="">
        <span class="auth-bowl-piece left" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
        <span class="auth-bowl-piece top-right" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
        <span class="auth-bowl-piece bottom-right" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
        <a class="auth-logo" href="{{ url('/') }}" aria-label="Mi Cusina Home">
            <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina">
        </a>
        <div class="auth-pattern" aria-hidden="true"></div>
        <span class="auth-zigzag one" aria-hidden="true"></span>
            <span class="auth-zigzag two" aria-hidden="true"></span>
            <span class="auth-zigzag three" aria-hidden="true"></span>
            <span class="auth-zigzag four" aria-hidden="true"></span>
        <span class="auth-faded-leaf" aria-hidden="true"></span>
        <span class="auth-faded-leaf bottom" aria-hidden="true"></span>

        <section class="auth-content">
            <img class="auth-bowl-art" src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt="" aria-hidden="true">
            <span class="auth-bowl-piece left" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
            <span class="auth-bowl-piece top-right" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
            <span class="auth-bowl-piece bottom-right" aria-hidden="true"><img src="{{ asset('assets/imgs/auth-three-bowls.png') }}" alt=""></span>
            <div class="auth-food" aria-hidden="true">
                <img src="{{ asset('food_img/auth-mi-cusina-combo.png') }}" alt="">
            </div>

            <form class="auth-card" method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf
                <h1>Welcome Back!</h1>

                @if ($errors->any())
                    <div class="auth-errors">
                        {{ $errors->first() }}
                    </div>
                @endif

                @session('status')
                    <div class="auth-errors">{{ $value }}</div>
                @endsession

                <div class="auth-field">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="off">
                </div>

                <div class="auth-field auth-password-field">
                    <input id="password" type="password" name="password" placeholder="Password" required autocomplete="off" minlength="8" maxlength="15">
                    <button class="auth-password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false" data-password-toggle>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="m3 3 18 18" data-password-slash></path>
                        </svg>
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <a class="auth-helper" href="{{ route('password.request') }}">Forgot password?</a>
                @endif

                <button class="auth-submit" type="submit">Login</button>
                <div class="auth-switch"><a href="{{ url('/?section=food') }}">Continue as guest</a><br>No registration needed to order.</div>

                <div class="auth-switch">
                    Don't have an account?
                    <a href="{{ route('register') }}">Sign up</a>
                </div>
            </form>
        </section>
    </main>
    <img
        src="{{ asset('assets/imgs/auth-three-bowls.png') }}"
        alt=""
        aria-hidden="true"
        style="display:block !important; height:100vh !important; inset:0 !important; max-width:none !important; object-fit:cover !important; pointer-events:none !important; position:fixed !important; visibility:visible !important; width:100vw !important; z-index:2001 !important;"
    >
</x-guest-layout>
