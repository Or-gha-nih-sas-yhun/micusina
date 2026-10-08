<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Mi Cusina restaurant ordering system.">
    <meta name="author" content="Devcrud">
    <title>Mi Cusina</title>
   
    <!-- font icons -->
    <link rel="stylesheet" href="assets/vendors/themify-icons/css/themify-icons.css">

    <link rel="stylesheet" href="assets/vendors/animate/animate.css">

    <!-- Bootstrap + Mi Cusina main styles -->
	<link rel="stylesheet" href="assets/css/foodhut.css">
    <style>
        .site-flash {
            animation: siteFlashOut .2s ease 1.8s forwards;
            background: #111;
            border: 1px solid #F88379;
            border-radius: 999px;
            box-shadow: 0 14px 36px rgba(0, 0, 0, .32);
            color: #fff;
            font-size: 17px;
            font-weight: 800;
            left: 50%;
            max-width: calc(100% - 32px);
            padding: 14px 24px;
            position: fixed;
            text-align: center;
            top: 28px;
            transform: translateX(-50%);
            z-index: 3000;
        }

        @keyframes siteFlashOut {
            to {
                opacity: 0;
                transform: translate(-50%, -8px);
                visibility: hidden;
            }
        }

        /* Shared customer typography — homepage, menu, cart, orders and booking. */
        :root { --mi-body-font: Arial, Helvetica, sans-serif; --mi-heading-font: Georgia, 'Times New Roman', serif; }
        html body,
        html body input,
        html body select,
        html body textarea,
        html body button {
            font-family: var(--mi-body-font) !important;
        }
        html body p,
        html body li,
        html body label,
        html body input,
        html body select,
        html body textarea {
            font-size: 16px !important;
            line-height: 1.5;
        }
        html body h1,
        html body h2,
        html body h3,
        html body h4,
        html body h5,
        html body h6 {
            font-family: var(--mi-heading-font) !important;
        }
        html body .nav-link,
        html body .burger-nav a,
        html body .burger-login a,
        html body .inner-navbar .nav-link {
            font-family: var(--mi-body-font) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
        }
        html body button,
        html body .btn,
        html body .burger-actions a,
        html body .auth-submit {
            font-family: var(--mi-body-font) !important;
            font-size: 14px !important;
            font-weight: 700 !important;
        }
    </style>

<link rel="stylesheet" href="{{ asset('assets/css/mi-cusina-theme.css') }}">
