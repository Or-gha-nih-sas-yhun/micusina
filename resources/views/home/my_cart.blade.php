@php
    $embeddedCart = request()->boolean('embed');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('home.css')

    <style>
        body {
            background: #000;
            color: #111;
            margin: 0;
        }

        .cart-topbar {
            align-items: center;
            background: #050505;
            display: inline-flex;
            justify-content: space-between;
            min-height: 96px;
            padding: 0 34px;
        }

        .cart-topbar a,
        .cart-topbar button {
            background: transparent;
            border: 0;
            color: #fff;
            cursor: pointer;
            font-size: 18px;
            font-weight: 800;
            padding: 0;
            text-decoration: none;
        }

        .cart-topbar a:hover,
        .cart-topbar button:hover {
            color: #F88379;
        }

        .cart-topbar form {
            margin: 0;
        }

        .cart-top-actions {
            align-items: center;
            display: flex;
            gap: 24px;
        }

        .cart-top-actions a {
            color: #fff;
            font-size: 18px;
            font-weight: 900;
            text-decoration: none;
        }

        .cart-top-actions a:hover {
            color: #F88379;
        }

        .cart-user-menu {
            position: relative;
        }

        .cart-user-menu summary {
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

        .cart-user-menu summary::-webkit-details-marker {
            display: none;
        }

        .cart-user-dropdown {
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
            z-index: 30;
        }

        .cart-user-info {
            border-bottom: 1px solid #2b2b2b;
            margin-bottom: 8px;
            padding: 8px 10px 12px;
        }

        .cart-user-info strong,
        .cart-user-info span {
            display: block;
        }

        .cart-user-info strong {
            color: #fff;
            font-size: 15px;
        }

        .cart-user-info span {
            color: #aaa;
            font-size: 13px;
            margin-top: 4px;
        }

        .cart-user-dropdown button {
            background: transparent;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 800;
            padding: 10px;
            text-align: left;
            width: fit-content;
        }

        .cart-user-dropdown a {
            border-radius: 6px;
            color: #fff;
            display: block;
            font-size: 14px;
            font-weight: 800;
            padding: 10px;
            text-decoration: none;
        }

        .cart-user-dropdown a:hover,
        .cart-user-dropdown button:hover {
            background: #F88379;
            color: #050505;
        }

        .cart-page {
            background: #000;
            min-height: calc(100vh - 96px);
            padding: 130px 28px 80px;
        }

        .cart-title {
            color: #fff;
            font-size: 34px;
            font-weight: 500;
            letter-spacing: 7px;
            margin: 0 0 28px;
            text-align: center;
            text-transform: uppercase;
        }

        .cart-shell {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(255, 255, 255, 0.08);
            margin: 0 auto;
            max-width: 1460px;
            padding: 44px;
        }

        .cart-brand {
            align-items: center;
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }

        .cart-brand img {
            height: 44px;
            object-fit: contain;
            width: 44px;
        }

        .cart-brand strong {
            color: #111;
            font-size: 24px;
            font-weight: 900;
        }

        .cart-heading {
            color: #000;
            font-size: 30px;
            font-weight: 900;
            margin: 0;
        }

        .cart-main {
            padding: 0;
        }

        .cart-main-head {
            align-items: center;
            border-bottom: 0;
            display: flex;
            justify-content: space-between;
            margin-bottom: 34px;
            padding-bottom: 0;
        }

        .cart-count {
            color: #222;
            font-size: 16px;
            font-weight: 900;
        }

        .cart-grid {
            align-items: start;
            display: grid;
            gap: 34px;
            grid-template-columns: minmax(0, 1fr) 390px;
        }

        .cart-list,
        .checkout-details {
            border: 1px solid #d8d8d8;
            border-radius: 18px;
            background: #fff;
        }

        .cart-list {
            border: 1px solid #d8d8d8;
            border-radius: 18px;
            overflow: hidden;
        }

        .cart-list-head,
        .cart-item {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(260px, 1fr) 170px 130px 130px;
            padding: 22px 24px;
        }

        .cart-list-head {
            border-bottom: 1px solid #d8d8d8;
            color: #222;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .cart-item {
            align-items: center;
            border-bottom: 1px solid #e5e5e5;
        }

        .cart-item:last-child {
            border-bottom: 0;
        }

        .cart-product {
            align-items: center;
            display: flex;
            gap: 16px;
        }

        .cart-product img { border-radius: 12px; height: 72px; object-fit: cover; width: 72px; }

        .cart-remove { background: transparent; border: 1px solid #d89191; border-radius: 999px; color: #b54b4b; cursor: pointer; font-size: 13px; font-weight: 800; margin-top: 7px; padding: 5px 10px; transition: background-color .18s ease, border-color .18s ease, color .18s ease; }
        .cart-remove:hover { background: #fff0f0; border-color: #b54b4b; color: #7f2020; text-decoration: none; }

        .cart-product strong {
            color: #000;
            display: block;
            font-size: 22px;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .cart-product span,
        .cart-muted {
            color: #777;
            font-size: 14px;
        }

        .qty-pill {
            align-items: center;
            border: 1px solid #111;
            border-radius: 999px;
            display: inline-flex;
            font-weight: 800;
            gap: 12px;
            justify-content: center;
            min-width: 82px;
            padding: 8px 12px;
        }

        .qty-stepper {
            align-items: center;
            border: 1px solid #d4d4d4;
            border-radius: 999px;
            display: inline-flex;
            gap: 10px;
            min-height: 42px;
            padding: 4px 8px;
        }

        .qty-stepper form {
            margin: 0;
        }

        .qty-stepper button {
            align-items: center;
            background: #fff;
            border: 0;
            border-radius: 999px;
            color: #111;
            cursor: pointer;
            display: inline-flex;
            font-size: 20px;
            font-weight: 800;
            height: 30px;
            justify-content: center;
            line-height: 1;
            width: 30px;
        }

        .qty-stepper button:hover {
            background: #111;
            color: #fff;
        }

        .qty-stepper button:disabled {
            color: #aaa;
            cursor: not-allowed;
        }

        .qty-stepper strong {
            color: #111;
            font-size: 16px;
            min-width: 20px;
            text-align: center;
        }

        .cart-price {
            color: #000;
            font-size: 20px;
            font-weight: 900;
        }

        .empty-cart {
            color: #555;
            font-size: 18px;
            padding: 44px 18px;
            text-align: center;
        }

        .continue-shopping {
            border: 1.5px solid #1f2937;
            border-radius: 999px;
            box-sizing: border-box;
            color: #F88379;
            display: inline-flex;
            font-size: 14px;
            font-weight: 800;
            margin-top: 28px;
            padding: 9px 14px;
            text-decoration: none;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease;
            width: auto;
        }

        .continue-shopping:hover {
            background: #fff1ef;
            border-color: #F88379;
            color: #111;
            text-decoration: none;
        }

        .order-summary,
        .checkout-details {
            padding: 24px;
        }

        .order-summary {
            align-self: stretch;
            background: #fff;
            border: 1px solid #d8d8d8;
            border-radius: 18px;
            min-height: 100%;
            padding: 34px 30px;
        }

        .order-summary h2,
        .checkout-details h2 {
            color: #000;
            font-size: 18px;
            font-weight: 900;
            margin: 0 0 22px;
        }

        .summary-line {
            align-items: center;
            color: #222;
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .summary-line strong {
            color: #000;
        }

        .summary-total {
            border-top: 1px solid #ddd;
            color: #000;
            display: flex;
            font-size: 24px;
            font-weight: 900;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 18px;
        }

        .checkout-button {
            align-items: center;
            background: #F88379;
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: flex;
            font-size: 16px;
            font-weight: 900;
            justify-content: center;
            margin-top: 24px;
            min-height: 54px;
            text-decoration: none;
            width: 100%;
        }

        .checkout-button:hover { color: #fff; text-decoration: none; }

        .checkout-button:disabled {
            background: #999;
            cursor: not-allowed;
            opacity: 1;
        }

        .checkout-details {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }

        .checkout-modal {
            align-items: center;
            background: rgba(0, 0, 0, 0.72);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 24px;
            position: fixed;
            z-index: 2000;
        }

        .checkout-modal.is-visible {
            display: flex;
        }

        .checkout-modal .checkout-details {
            max-width: 520px;
            width: 100%;
        }

        .checkout-modal-head {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .checkout-modal-head h2 {
            margin: 0;
        }

        .checkout-close {
            align-items: center;
            background: #000;
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 22px;
            height: 38px;
            justify-content: center;
            line-height: 1;
            width: 38px;
        }

        .checkout-form {
            display: grid;
            gap: 14px;
        }

        .form-row label {
            color: #222;
            display: block;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 7px;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            background: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 12px;
            color: #000;
            font-size: 15px;
            min-height: 46px;
            padding: 10px 12px;
            width: 100%;
        }

        .form-row textarea {
            min-height: 88px;
            resize: vertical;
        }

        .payment-qr-panel,
        .payment-receipt {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 14px;
            display: none;
            padding: 16px;
        }

        .payment-qr-panel.is-visible,
        .payment-receipt.is-visible {
            display: block;
        }

        .payment-qr-panel h3,
        .payment-receipt h3 {
            color: #000;
            font-size: 16px;
            margin: 0 0 10px;
        }

        .payment-qr-panel img {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            display: none;
            max-width: 180px;
            padding: 8px;
            width: 100%;
        }

        .payment-qr-panel p,
        .payment-receipt p {
            color: #555;
            margin: 8px 0;
        }

        .payment-receipt strong {
            color: #000;
        }

        @media (max-width: 991.98px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }

            .cart-list-head {
                display: none;
            }

            .cart-item {
                grid-template-columns: 1fr;
            }

            .cart-grid {
                gap: 24px;
            }
        }

        @media (max-width: 575.98px) {
            .cart-shell {
                border-radius: 16px;
                padding: 22px;
            }

            .cart-heading {
                font-size: 34px;
            }
        }
        /* Keep cart typography aligned with the customer homepage. */
        .cart-topbar a,
        .cart-topbar button,
        .cart-shell,
        .cart-shell input,
        .cart-shell select,
        .cart-shell textarea,
        .cart-shell button,
        .cart-shell label {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body.cart-embed {
            background: #fff;
            padding: 0;
        }

        .cart-embed .cart-page {
            min-height: 100vh;
            padding-top: 24px;
        }

        .cart-embed .cart-title {
            display: none;
        }

        .cart-topbar a,
        .cart-topbar button {
            font-size: 15px !important;
        }

        .cart-shell p,
        .cart-shell li,
        .cart-shell label,
        .cart-shell input,
        .cart-shell select,
        .cart-shell textarea {
            font-size: 17px !important;
            line-height: 1.6;
        }

        .cart-shell button,
        .cart-shell .btn,
        .cart-shell a.button {
            font-size: 14px !important;
            font-weight: 700 !important;
        }

        /* Compact card layout used when the cart opens above the food menu. */
        body.cart-embed {
            background: #fff;
            height: auto;
            overflow: auto;
        }

        body.cart-embed .cart-page {
            display: block;
            height: auto;
            min-height: 100%;
            padding: 0;
        }

        body.cart-embed .cart-shell {
            border-radius: 0;
            box-shadow: none;
            display: block;
            min-height: 0;
            overflow: visible;
        }

        body.cart-embed .cart-grid { display: block; height: auto; }
        body.cart-embed .cart-main { display: block; padding: 64px 16px 8px; }
        body.cart-embed .cart-main-head { align-items: center; display: flex; justify-content: center; margin-bottom: 14px; padding: 0 42px 14px; }
        body.cart-embed .cart-heading { font-family: Arial, Helvetica, sans-serif !important; font-size: 17px !important; font-weight: 800 !important; }
        body.cart-embed .cart-count { display: none; }
        body.cart-embed .cart-list { overflow: visible; }
        body.cart-embed .cart-list-head { display: none; }

        body.cart-embed .cart-item {
            background: #f0e2ef;
            border: 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(34, 18, 36, .16);
            display: grid;
            gap: 4px 12px;
            grid-template-columns: minmax(0, 1fr) auto;
            margin-bottom: 10px;
            padding: 12px;
        }

        body.cart-embed .cart-product { gap: 12px; grid-column: 1; grid-row: 1 / span 2; min-width: 0; }
        body.cart-embed .cart-product img { border-radius: 6px; height: 62px; width: 62px; }
        body.cart-embed .cart-product strong { font-size: 12px !important; font-weight: 800 !important; margin: 0 0 3px; }
        body.cart-embed .cart-product span { font-size: 10px !important; line-height: 1.25 !important; }
        body.cart-embed .cart-remove { font-size: 10px !important; margin-top: 4px; padding: 3px 7px; }
        body.cart-embed .cart-item > div:nth-child(2) { align-self: start; grid-column: 2; grid-row: 1; }
        body.cart-embed .cart-item > div:nth-child(3) { display: none; }
        body.cart-embed .cart-item > div:nth-child(4) { align-self: end; grid-column: 2; grid-row: 2; }
        body.cart-embed .cart-price { font-size: 13px !important; font-weight: 800 !important; }
        body.cart-embed .qty-stepper { gap: 3px; min-height: 27px; padding: 1px 3px; }
        body.cart-embed .qty-stepper button { font-size: 14px !important; height: 22px; width: 22px; }
        body.cart-embed .qty-stepper strong { font-size: 12px !important; }
        body.cart-embed .continue-shopping { display: none; }

        body.cart-embed aside { display: block; padding: 0 16px 18px; }
        body.cart-embed .order-summary { background: #fff; border: 0; border-radius: 0; padding: 12px 0 0; }
        body.cart-embed .order-summary h2 { font-family: Arial, Helvetica, sans-serif !important; font-size: 14px !important; font-weight: 800 !important; margin-bottom: 10px; }
        body.cart-embed .summary-line { font-size: 11px !important; padding: 5px 0; }
        body.cart-embed .summary-line:nth-of-type(3) { display: none; }
        body.cart-embed .summary-total { font-size: 15px !important; margin-top: 8px; padding-top: 12px; }
        body.cart-embed .checkout-button { background: #9b4698 !important; border-radius: 5px; font-size: 14px !important; margin-top: 18px; min-height: 44px; }

        /* Desktop cart: keep the whole checkout in one screen, like a POS cart. */
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background: #e9eaee;
        }

        .cart-page {
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 96px);
            min-height: 0;
            padding: 24px clamp(18px, 5vw, 72px);
        }

        /* Keep the standalone cart below the fixed navbar without a page title. */
        body:not(.cart-embed) .cart-page {
            padding-top: 120px;
        }

        .cart-title {
            color: #20232b;
            font-size: 24px !important;
            line-height: 1 !important;
            margin: 0 0 16px;
        }

        .cart-shell {
            border-radius: 0;
            box-shadow: 0 22px 48px rgba(22, 30, 48, .16);
            display: flex;
            flex: 1;
            margin: 0 auto;
            max-width: 1280px;
            min-height: 0;
            overflow: hidden;
            padding: 0;
            width: 100%;
        }

        .cart-grid {
            gap: 0;
            grid-template-columns: minmax(0, 1fr) 340px;
            height: 100%;
            min-height: 0;
            width: 100%;
        }

        .cart-main {
            display: flex;
            flex-direction: column;
            min-height: 0;
            padding: 32px clamp(24px, 3vw, 48px);
        }

        .cart-brand { display: none; }
        .cart-main-head { border-bottom: 1px solid #e7e7e7; margin-bottom: 14px; padding-bottom: 16px; }
        .cart-heading { font-size: 24px !important; }
        .cart-count { font-size: 14px !important; }

        .cart-list,
        .checkout-details { border: 0; border-radius: 0; }

        .cart-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }

        .cart-list-head,
        .cart-item {
            gap: 12px;
            grid-template-columns: minmax(220px, 1fr) 120px 90px 90px;
            padding: 13px 0;
        }

        .cart-list-head { font-size: 10px !important; padding-top: 4px; }
        .cart-product { gap: 12px; }
        .cart-product img { border-radius: 2px; height: 58px; width: 58px; }
        .cart-product strong { font-size: 14px !important; margin-bottom: 2px; }
        .cart-product span, .cart-muted, .cart-remove { font-size: 11px !important; }
        .cart-price { font-size: 13px !important; }
        .qty-stepper { border-radius: 4px; gap: 5px; min-height: 30px; padding: 2px 4px; }
        .qty-stepper button { font-size: 16px; height: 24px; width: 24px; }
        .qty-stepper strong { font-size: 13px !important; }
        .continue-shopping { flex: 0 0 auto; font-size: 12px !important; margin-top: 16px; padding: 7px 11px; }

        .order-summary {
            background: #f7f7f8;
            border: 0;
            border-left: 1px solid #ececef;
            border-radius: 0;
            min-height: 0;
            padding: 32px 24px;
        }

        .order-summary h2 { font-size: 18px !important; margin-bottom: 22px; }
        .summary-line { border-top: 1px solid #e4e4e7; font-size: 12px !important; margin: 0; padding: 14px 0; }
        .summary-total { font-size: 15px !important; margin-top: 10px; padding-top: 16px; }
        .checkout-button { border-radius: 2px; font-size: 12px !important; margin-top: 22px; min-height: 44px; }

        body.cart-embed .cart-page {
            height: 100vh;
            padding: 0;
        }

        @media (max-width: 991.98px) {
            html, body { height: auto; overflow: auto; }
            .cart-page, body.cart-embed .cart-page { height: auto; min-height: 100vh; }
            .cart-shell { display: block; overflow: visible; }
            .cart-grid { display: block; height: auto; }
            .cart-main { display: block; }
            .cart-list { overflow: visible; }
            .order-summary { border-left: 0; border-top: 1px solid #ececef; }
        }

        .cart-shell .checkout-button {
            background: #ed0da8 !important;
            border-color: #ed0da8 !important;
            color: #fff !important;
            box-shadow: 0 12px 25px rgba(237, 13, 168, .25) !important;
        }

        .cart-shell .checkout-button:hover { background: #d90a99 !important; }

        .gcash-open-button,
        .bank-open-button {
            align-items: center;
            background: #0867e8 !important;
            border: 2px solid #000 !important;
            border-radius: 999px;
            color: #fff !important;
            display: none;
            font-size: 16px !important;
            font-weight: 700 !important;
            justify-content: center;
            margin: 0 0 18px;
            min-height: 54px;
            text-decoration: none;
            width: 100%;
        }

        .gcash-open-button.is-visible,
        .bank-open-button.is-visible { display: flex; }
        .gcash-open-button:hover { background: #0057cc !important; color: #fff !important; }
        .bank-open-button { background: #15803d !important; }
        .bank-open-button:hover { background: #116b33 !important; color: #fff !important; }
        .payment-qr-panel img { border: 2px solid #000 !important; }

        .cart-shell .qty-stepper button {
            background: #111 !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        .cart-shell .qty-stepper button:hover { background: #000 !important; color: #fff !important; }
        .cart-shell .qty-stepper button:disabled { background: #777 !important; color: #fff !important; }

        .cart-title,
        .cart-heading,
        .order-summary h2,
        .checkout-details h2,
        .checkout-modal-head h2 {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 700 !important;
        }

        .cart-title {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: clamp(52px, 4.5vw, 82px) !important;
            font-weight: 700 !important;
            letter-spacing: 0 !important;
            text-transform: none !important;
        }
        .cart-heading { font-size: 32px !important; }
        .order-summary h2,
        .checkout-details h2,
        .checkout-modal-head h2 { font-size: 30px !important; }

        .cart-brand strong,
        .cart-item-name,
        .summary-line,
        .summary-total,
        .cart-count {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* Exact homepage type scale and weight. */
        .cart-title {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: clamp(55px, 5vw, 88px) !important;
            font-weight: 400 !important;
            letter-spacing: -.055em !important;
            line-height: .93 !important;
        }

        .cart-heading,
        .order-summary h2,
        .checkout-details h2,
        .checkout-modal-head h2 {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 400 !important;
            letter-spacing: -.025em !important;
        }

        .cart-shell,
        .cart-shell * {
            font-family: Arial, Helvetica, sans-serif !important;
            font-weight: 400 !important;
        }

        .cart-shell p,
        .cart-shell span,
        .cart-shell td,
        .cart-shell th,
        .cart-shell label,
        .cart-shell input,
        .cart-shell select {
            font-size: 17px !important;
            line-height: 1.6 !important;
        }

        .cart-shell .cart-heading,
        .cart-shell .order-summary h2,
        .cart-shell .checkout-details h2,
        .cart-shell .checkout-modal-head h2 {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 400 !important;
        }

        .cart-shell button,
        .cart-shell .btn,
        .cart-shell a.button {
            font-size: 14px !important;
            font-weight: 700 !important;
        }
    </style>
</head>

<body class="{{ $embeddedCart ? 'cart-embed' : 'content-page' }}">
    @if(!$embeddedCart)
        @include('home.header', ['forceInnerNavbar' => true])
    @endif

    <main class="cart-page">
        <section class="cart-shell">
            @php
                $total_price = 0;
                $total_items = 0;
            @endphp

            @foreach ($data as $cart)
                @php
                    $lineTotal = (float) $cart->price;
                    $total_price = ($total_price ?? 0) + $lineTotal;
                    $total_items = ($total_items ?? 0) + (int) $cart->quantity;
                @endphp
            @endforeach

                <div class="cart-grid">
                    <div class="cart-main">
                        <div class="cart-brand">
                            <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina logo">
                            <strong>Mi Cusina</strong>
                        </div>

                        <div class="cart-main-head">
                            <h2 class="cart-heading">Shopping Cart</h2>
                            <span class="cart-count">{{ $total_items }} {{ \Illuminate\Support\Str::plural('Item', $total_items) }}</span>
                        </div>

                        <div class="cart-list">
                            <div class="cart-list-head">
                                <div>Product Details</div>
                                <div>Quantity</div>
                                <div>Price</div>
                                <div>Total</div>
                            </div>

                            @forelse ($data as $cart)
                                @php
                                    $quantity = max(1, (int) $cart->quantity);
                                    $lineTotal = (float) $cart->price;
                                    $unitPrice = $lineTotal / $quantity;
                                    $decreaseQty = max(1, $quantity - 1);
                                    $increaseQty = $quantity + 1;
                                @endphp
                                <div class="cart-item">
                                    <div class="cart-product">
                                        @if($cart->image)
                                            <img src="{{ asset('food_img/' . $cart->image) }}" alt="{{ $cart->title }}" onerror="this.style.display='none'">
                                        @endif
                                        <div>
                                            <strong>{{ $cart->title }}</strong>
                                            <span>&#8369;{{ number_format($unitPrice, 2) }} each</span>
                                            <form action="{{ url('remove_cart', $cart->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="cart-remove" type="submit">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="qty-stepper" aria-label="Quantity for {{ $cart->title }}">
                                            <form action="{{ url('update_cart', $cart->id) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="quantity" value="{{ $decreaseQty }}">
                                                <button type="submit" {{ $quantity <= 1 ? 'disabled' : '' }} aria-label="Decrease {{ $cart->title }}">-</button>
                                            </form>
                                            <strong>{{ $quantity }}</strong>
                                            <form action="{{ url('update_cart', $cart->id) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="quantity" value="{{ $increaseQty }}">
                                                <button type="submit" aria-label="Increase {{ $cart->title }}">+</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="cart-price">&#8369;{{ number_format($unitPrice, 2) }}</div>
                                    <div class="cart-price">&#8369;{{ number_format($lineTotal, 2) }}</div>
                                </div>
                            @empty
                                <div class="empty-cart">Your cart is empty.</div>
                            @endforelse
                        </div>

                        <a class="continue-shopping" href="{{ url('/?section=food') }}">&larr; Continue Shopping</a>
                    </div>

                    <aside>
                        <div class="order-summary">
                            <h2>Order Summary</h2>
                            <div class="summary-line">
                                <span>Items</span>
                                <strong>{{ $total_items }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Sub Total</span>
                                <strong>&#8369;{{ number_format($total_price, 2) }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Delivery Fee</span>
                                <strong>To be confirmed</strong>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span>&#8369;{{ number_format($total_price, 2) }}</span>
                            </div>
                            <a class="checkout-button" href="{{ route('checkout') }}" target="{{ $embeddedCart ? '_top' : '_self' }}" {{ $data->isEmpty() ? 'aria-disabled=true' : '' }}>Proceed to Checkout</a>
                        </div>
                    </aside>
                </div>

                <div class="checkout-modal" id="checkoutModal">
                    <div class="checkout-details" role="dialog" aria-modal="true" aria-labelledby="checkoutTitle">
                        <div class="checkout-modal-head">
                            <h2 id="checkoutTitle">Customer Details & Payment</h2>
                            <button class="checkout-close" id="closeCheckout" type="button" aria-label="Close checkout">&times;</button>
                        </div>

                        <form action="{{ url('confirm_order') }}" method="post">
                            @csrf
                            <div class="checkout-form">
                                <input type="hidden" name="email" value="{{ Auth()->user()->email }}">

                                <div class="form-row">
                                    <label for="name">Name</label>
                                    <input id="name" type="text" name="name" value="" required>
                                </div>

                                <div class="form-row">
                                    <label for="phone">Phone</label>
                                    <input id="phone" type="tel" name="phone" value="" inputmode="tel"
                                        pattern="\+639[0-9]{9}" maxlength="13" required
                                        oninput="this.value = '+639' + this.value.replace(/[^0-9]/g, '').replace(/^639/, '').slice(0, 9)">
                                </div>

                                <div class="form-row">
                                    <label for="municipality">Municipality</label>
                                    <select id="municipality" name="municipality" required>
                                        <option value="">Select municipality</option>
                                        <option value="Bantayan">Bantayan</option>
                                        <option value="Madridejos">Madridejos</option>
                                        <option value="Santa Fe">Santa Fe</option>
                                    </select>
                                </div>

                                <div class="form-row">
                                    <label for="barangay">Barangay</label>
                                    <select id="barangay" name="barangay" required>
                                        <option value="">Select barangay</option>
                                    </select>
                                </div>

                                <div class="form-row">
                                    <label for="purok">Purok</label>
                                    <input id="purok" type="text" name="purok" required>
                                </div>

                                <div class="form-row">
                                    <label for="address_details">Details</label>
                                    <textarea id="address_details" name="address_details" placeholder="House color, landmark, gate number, or delivery notes"></textarea>
                                </div>

                                <div class="form-row">
                                    <label for="payment_method">Payment Method</label>
                                    <select id="payment_method" name="payment_method" required>
                                        <option value="Cash on Delivery">Cash on Delivery</option>
                                        <option value="GCash">GCash</option>
                                    </select>
                                </div>

                                <div class="payment-qr-panel" id="paymentQrPanel">
                                    <h3 id="paymentQrTitle">Payment QR</h3>
                                    <a class="gcash-open-button" id="openGcashButton" href="gcash://" aria-label="Open GCash app">Open in GCash</a>
                                    <img id="paymentQrImage" src="" alt="Payment QR">
                                    <p id="paymentQrMissing" style="display:none;">QR image not found. Please ask staff for payment details.</p>
                                </div>

                                <button class="checkout-button" type="submit">Confirm Order</button>
                            </div>
                        </form>
                    </div>
                </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var barangays = {
                'Bantayan': [
                    'Atop-atop', 'Baigad', 'Baod', 'Binaobao', 'Botigues', 'Doong', 'Guiwanon',
                    'Hilotongan', 'Kabac', 'Kabangbang', 'Kampingganon', 'Kangkaibe', 'Lipayran',
                    'Luyongbaybay', 'Mojon', 'Obo-ob', 'Patao', 'Putian', 'Sillon', 'Suba',
                    'Sulangan', 'Sungko', 'Tamiao', 'Ticad'
                ],
                'Madridejos': [
                    'Bunakan', 'Kangwayan', 'Kaongkod', 'Kodia', 'Maalat', 'Malbago',
                    'Mancilang', 'Pili', 'Poblacion', 'San Agustin', 'Tabagak',
                    'Talangnan', 'Tarong', 'Tugas'
                ],
                'Santa Fe': [
                    'Balidbid', 'Hagdan', 'Hilantagaan', 'Kinatarkan', 'Langub',
                    'Maricaban', 'Okoy', 'Poblacion', 'Pooc', 'Talisay'
                ]
            };

            var municipality = document.getElementById('municipality');
            var barangay = document.getElementById('barangay');
            var paymentMethod = document.getElementById('payment_method');
            var paymentQrPanel = document.getElementById('paymentQrPanel');
            var paymentQrTitle = document.getElementById('paymentQrTitle');
            var paymentQrImage = document.getElementById('paymentQrImage');
            var paymentQrMissing = document.getElementById('paymentQrMissing');
            var openGcashButton = document.getElementById('openGcashButton');
            var checkoutModal = document.getElementById('checkoutModal');
            var openCheckout = document.getElementById('openCheckout');
            var closeCheckout = document.getElementById('closeCheckout');

            function showCheckout() {
                checkoutModal.classList.add('is-visible');
                document.body.style.overflow = 'hidden';
                document.getElementById('name').focus();
            }

            function hideCheckout() {
                checkoutModal.classList.remove('is-visible');
                document.body.style.overflow = '';
            }

            if (openCheckout) {
                openCheckout.addEventListener('click', showCheckout);
            }

            closeCheckout.addEventListener('click', hideCheckout);
            checkoutModal.addEventListener('click', function (event) {
                if (event.target === checkoutModal) {
                    hideCheckout();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && checkoutModal.classList.contains('is-visible')) {
                    hideCheckout();
                }
            });

            function fillBarangays() {
                var selected = municipality.value;
                barangay.innerHTML = '<option value="">Select barangay</option>';

                (barangays[selected] || []).forEach(function (name) {
                    var option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    barangay.appendChild(option);
                });
            }

            municipality.addEventListener('change', fillBarangays);

            function updatePaymentFields() {
                var method = paymentMethod.value;
                var needsReference = method === 'GCash';

                paymentQrPanel.classList.toggle('is-visible', needsReference);
                openGcashButton.classList.toggle('is-visible', method === 'GCash');

                if (!needsReference) {
                    paymentQrImage.style.display = 'none';
                    return;
                }

                paymentQrTitle.textContent = method + ' QR Payment';
                paymentQrImage.src = '{{ asset('payment/gcash-qr.jpg') }}';
                paymentQrImage.style.display = 'block';
                paymentQrMissing.style.display = 'none';
            }

            paymentQrImage.addEventListener('error', function () {
                paymentQrImage.style.display = 'none';
                paymentQrMissing.style.display = 'block';
            });

            paymentMethod.addEventListener('change', updatePaymentFields);
            updatePaymentFields();
        });
    </script>
</body>
</html>
