<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dark Bootstrap Admin </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="admin/vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="admin/vendor/font-awesome/css/font-awesome.min.css">
    <!-- Custom Font Icons CSS-->
    <link rel="stylesheet" href="admin/css/font.css">
    <!-- Google fonts - Muli-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="admin/css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="admin/css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="admin/img/favicon.ico">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
    <style>
        :root {
            --mc-bg: #050505;
            --mc-shell: #0c0d10;
            --mc-panel: #15171c;
            --mc-panel-soft: #1f2229;
            --mc-border: #2b2f38;
            --mc-border-strong: #3a3f4a;
            --mc-text: #ffffff;
            --mc-muted: #a6abb6;
            --mc-accent: #F88379;
            --mc-green: #F88379;
            --mc-red: #ef4444;
            --mc-orange: #F88379;
            --mc-blue: #60a5fa;
        }

        body,
        .page-content,
        .page-header,
        .container-fluid {
            background: var(--mc-bg);
            color: var(--mc-text);
        }

        .page-content {
            min-height: calc(100vh - 70px);
        }

        .header .navbar {
            background: #08090c;
            min-height: 0;
            padding: 8px 20px;
        }

        .header .container-fluid {
            justify-content: space-between;
            width: 100%;
        }

        .header .navbar-header,
        .header .navbar-brand {
            display: none !important;
        }

        .admin-brand {
            align-items: center;
            color: var(--mc-text);
            display: inline-flex;
            font-size: 20px;
            font-weight: 800;
            gap: 10px;
            text-decoration: none;
        }

        .admin-brand:hover {
            color: var(--mc-text);
            text-decoration: none;
        }

        .admin-brand img {
            height: 42px;
            object-fit: contain;
            width: 42px;
        }

        .admin-user {
            margin-left: auto;
        }

        .admin-actions { align-items: center; display: flex; gap: 12px; margin-left: auto; }
        .admin-user { margin-left: 0; }
        .admin-notifications { position: relative; }
        .admin-notification-trigger { align-items:center; background:#f3e8ff; border:1px solid #c084fc; border-radius:999px; color:#7c3aed; cursor:pointer; display:inline-flex; font-size:18px; height:46px; justify-content:center; position:relative; width:46px; }
        .admin-notification-trigger:hover { background:#ede9fe; border-color:#7c3aed; color:#5b21b6; }
        .admin-notification-trigger .admin-notification-count { align-items:center !important; background:#e5252a !important; border:2px solid #fff !important; border-radius:50% !important; box-sizing:border-box; color:#fff !important; display:flex !important; font-size:10px !important; font-weight:800 !important; height:20px !important; justify-content:center !important; line-height:1 !important; min-width:20px !important; padding:0 3px !important; position:absolute !important; right:-7px !important; top:-7px !important; z-index:20 !important; }
        .admin-notification-menu { max-height:360px; min-width:310px; overflow-y:auto; }
        .admin-notification-title { color:var(--mc-text); font-size:14px; font-weight:800; padding:8px 10px; }
        .admin-notification-item { align-items:center; color:var(--mc-text); display:flex; gap:10px; padding:10px; white-space:normal; }
        .admin-notification-item:hover { background:rgba(248, 131, 121, .12); color:var(--mc-text); text-decoration:none; }
        .admin-notification-item i { color:var(--mc-orange); font-size:17px; }
        .admin-notification-item small { color:var(--mc-muted); display:block; margin-top:2px; }
        .admin-notification-empty { color:var(--mc-muted); padding:10px; }
        .admin-sidebar-count { align-items:center; background:#e5252a !important; border:2px solid #fff !important; border-radius:999px; color:#fff !important; display:inline-flex; font-size:10px; font-weight:800; height:20px; justify-content:center; line-height:1; margin-left:auto; min-width:20px; padding:0 5px; }
        nav#sidebar li > a { align-items:center; display:flex; }

        .admin-user-trigger {
            align-items: center;
            background: transparent;
            border: 1px solid var(--mc-border-strong);
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            height: 46px;
            justify-content: center;
            padding: 3px;
            width: 46px;
        }

        .admin-user-trigger::after {
            display: none;
        }

        .admin-user-trigger img {
            border-radius: 999px;
            height: 38px;
            object-fit: cover;
            width: 38px;
        }

        .admin-user-menu {
            background: var(--mc-panel);
            border: 1px solid var(--mc-border);
            border-radius: 8px;
            color: var(--mc-text);
            margin-top: 10px;
            min-width: 220px;
            padding: 8px;
        }

        .admin-user-info {
            padding: 8px 10px;
        }

        .admin-user-info strong,
        .admin-user-info span {
            display: block;
        }

        .admin-user-info span {
            color: var(--mc-muted);
            font-size: 13px;
            margin-top: 2px;
        }

        .admin-user-menu .dropdown-divider {
            border-top-color: var(--mc-border);
        }

        .admin-user-menu .dropdown-item {
            background: transparent;
            border: 0;
            color: var(--mc-text);
            cursor: pointer;
            padding: 9px 10px;
            text-align: left;
            width: 100%;
        }

        .admin-user-menu .dropdown-item:hover {
            background: var(--mc-accent);
            color: #050505;
        }

        .admin-user-menu .dropdown-item.home-action,
        .admin-user-menu .dropdown-item.logout-action {
            background: #fff7f7 !important;
            border-bottom: 2px solid #ff746c !important;
            border-left: 2px solid #ff746c !important;
            border-right: 2px solid #ff746c !important;
            border-top: 2px solid #ff746c !important;
            border-radius: 8px;
            box-sizing: border-box;
            box-shadow: inset 0 0 0 1px #ff746c;
            display: block;
            margin: 0;
            min-height: 52px;
            width: 100%;
        }

        .admin-user-menu .dropdown-item.home-action {
            color: var(--mc-text) !important;
            margin-bottom: 8px;
        }

        .admin-user-menu .dropdown-item.home-action:hover,
        .admin-user-menu .dropdown-item.logout-action:hover {
            background: #ffecec !important;
            color: var(--mc-text) !important;
        }

        .admin-photo-form { margin: 0; }

        .admin-photo-label {
            align-items: center;
            border-radius: 8px;
            color: var(--mc-text);
            cursor: pointer;
            display: flex;
            font-size: 13px;
            font-weight: 700;
            gap: 9px;
            margin: 0;
            padding: 10px;
        }

        .admin-photo-label:hover {
            background: rgba(255, 33, 79, .16);
            color: #FFA69E;
        }

        .admin-photo-error {
            color: #FFA69E;
            display: block;
            font-size: 11px;
            padding: 4px 10px 8px;
        }


        .header,
        nav#sidebar {
            background: #08090c;
        }

        .navbar-brand,
        .header .navbar,
        nav#sidebar a,
        nav#sidebar .title h1,
        nav#sidebar .heading {
            color: var(--mc-text);
        }

        nav#sidebar .title p,
        nav#sidebar a:hover {
            color: var(--mc-muted);
        }

        nav#sidebar ul li > a i,
        .header .navbar i,
        .admin-table th::before,
        .staff-table th::before,
        .users-table th::before,
        .inventory-table th::before,
        .orders-table th::before,
        .reservation-table th::before,
        .foods-table th::before {
            color: var(--mc-accent);
        }

        nav#sidebar ul li:nth-child(2n) > a i {
            color: #60a5fa;
        }

        nav#sidebar ul li:nth-child(3n) > a i {
            color: #2dd4bf;
        }

        nav#sidebar ul li:nth-child(4n) > a i {
            color: #F88379;
        }

        nav#sidebar ul li.active > a i,
        nav#sidebar ul li > a:hover i {
            color: #F88379;
        }

        .admin-panel h1 i,
        .admin-panel h2 i,
        .admin-panel h3 i,
        .table-wrap i,
        .table-responsive i {
            color: #F88379;
        }

        .admin-surface {
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), var(--mc-shell);
            border: 1px solid var(--mc-border);
            border-radius: 8px;
            color: var(--mc-text);
            margin: 0;
            min-height: calc(100vh - 120px);
            padding: 28px;
        }

        .admin-surface h1,
        .admin-surface h2,
        .admin-surface h3 {
            color: var(--mc-text);
            font-weight: 800;
        }

        .admin-panel {
            background: var(--mc-panel);
            border: 1px solid var(--mc-border);
            border-radius: 8px;
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        .table-wrap,
        .table-responsive {
            overflow-x: visible;
            width: 100%;
        }

        .admin-table,
        .staff-table,
        .users-table,
        .inventory-table,
        .orders-table,
        .reservation-table,
        .foods-table {
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            width: 100%;
        }

        .admin-table th,
        .staff-table th,
        .users-table th,
        .inventory-table th,
        .orders-table th,
        .reservation-table th,
        .foods-table th {
            background: #101116;
            border-bottom: 1px solid var(--mc-border);
            color: var(--mc-muted);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            padding: 14px 16px;
            text-align: left;
            text-transform: uppercase;
            white-space: normal;
            word-break: break-word;
        }

        .admin-table td,
        .staff-table td,
        .users-table td,
        .inventory-table td,
        .orders-table td,
        .reservation-table td,
        .foods-table td {
            background: var(--mc-panel);
            border-top: 1px solid var(--mc-border);
            color: var(--mc-text);
            padding: 14px 16px;
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .admin-table tr:hover td,
        .staff-table tr:hover td,
        .users-table tr:hover td,
        .inventory-table tr:hover td,
        .orders-table tr:hover td,
        .reservation-table tr:hover td,
        .foods-table tr:hover td {
            background: var(--mc-panel-soft);
        }

        label {
            color: var(--mc-muted);
            font-weight: 700;
        }

        input,
        textarea,
        select,
        .form-control {
            background: #0f1015;
            border: 1px solid var(--mc-border-strong);
            border-radius: 6px;
            color: var(--mc-text);
        }

        input:focus,
        textarea:focus,
        select:focus,
        .form-control:focus {
            background: #0f1015;
            border-color: var(--mc-accent);
            box-shadow: 0 0 0 3px rgba(255, 33, 79, 0.16);
            color: var(--mc-text);
        }

        .btn-primary,
        .btn-warning,
        .btn-info {
            background: var(--mc-panel-soft);
            border-color: var(--mc-border-strong);
            color: var(--mc-text);
        }

        .btn-primary:hover,
        .btn-warning:hover,
        .btn-info:hover {
            background: var(--mc-accent);
            border-color: var(--mc-accent);
            color: #050505;
        }

        .d-flex.align-items-stretch {
            display: block !important;
        }

        nav#sidebar {
            align-items: center;
            border-bottom: 1px solid #1f242c;
            border-right: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-left: 0 !important;
            min-height: auto;
            padding: 12px 24px 14px;
            width: 100% !important;
        }

        nav#sidebar .sidebar-header,
        nav#sidebar span.heading {
            display: none !important;
        }

        nav#sidebar > ul.list-unstyled {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            max-height: none;
            width: 100%;
        }

        nav#sidebar li {
            position: relative;
        }

        nav#sidebar li::before {
            display: none;
        }

        nav#sidebar li a {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 999px;
            color: var(--mc-text);
            display: inline-flex;
            gap: 8px;
            padding: 9px 14px;
            white-space: nowrap;
        }

        nav#sidebar li a i {
            border-right: 0;
            margin-right: 0;
            padding-right: 0;
        }

        nav#sidebar li.active > a,
        nav#sidebar li a:hover {
            background: rgba(255, 33, 79, 0.18);
            border-color: rgba(255, 33, 79, 0.45);
            color: var(--mc-accent);
        }

        nav#sidebar li.active i,
        nav#sidebar li a:hover i {
            color: var(--mc-accent);
        }

        nav#sidebar ul ul {
            background: var(--mc-panel);
            border: 1px solid var(--mc-border);
            border-radius: 14px;
            left: 0;
            min-width: 180px;
            padding: 10px;
            position: absolute;
            top: calc(100% + 8px);
            z-index: 1000;
        }

        nav#sidebar .food-menu > a[aria-expanded="true"] {
            background: rgba(255, 33, 79, 0.16);
            border-color: rgba(255, 33, 79, 0.45);
            color: var(--mc-accent);
        }

        nav#sidebar ul ul li,
        nav#sidebar ul ul a {
            width: 100%;
        }

        nav#sidebar li li a {
            background: transparent;
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
        }

        nav#sidebar li li.active a {
            color: var(--mc-accent);
        }

        nav#sidebar a[data-toggle="collapse"]::after {
            display: none;
        }

        .page-content,
        .page-content.active {
            width: 100% !important;
        }

        .page-header {
            margin-bottom: 0;
            padding: 12px 20px 18px;
        }

        @media (max-width: 767px) {
            nav#sidebar {
                padding: 10px 14px;
            }

            nav#sidebar > ul.list-unstyled {
                overflow-x: auto;
                flex-wrap: nowrap;
            }
        }
    </style>

    <style>
        /* Polished light-theme accents */
        :root { --mi-primary:#f25f5c; --mi-primary-dark:#d94845; --mi-soft:#fff1f0; }
        html body .admin-brand, html body nav#sidebar ul li > a i,
        html body .admin-panel h1 i, html body .admin-panel h2 i, html body .admin-panel h3 i { color:var(--mi-primary) !important; }
        html body nav#sidebar li.active > a, html body nav#sidebar li a:hover,
        html body nav#sidebar .food-menu > a[aria-expanded="true"] {
            background:var(--mi-soft) !important; color:var(--mi-primary-dark) !important;
        }
        html body .btn-primary, html body .btn-danger, html body .btn-warning,
        html body .btn-info, html body button[type="submit"] {
            background:var(--mi-primary) !important; border-color:var(--mi-primary) !important; color:#fff !important;
            box-shadow:0 5px 14px rgba(242,95,92,.2);
        }
        html body .btn-primary:hover, html body .btn-danger:hover, html body .btn-warning:hover,
        html body .btn-info:hover, html body button[type="submit"]:hover {
            background:var(--mi-primary-dark) !important; border-color:var(--mi-primary-dark) !important;
        }
        html body .admin-panel, html body .sales-card, html body .mini-card, html body .chart-panel {
            border:1px solid #e5e7eb !important; box-shadow:0 10px 24px rgba(31,41,55,.08) !important;
        }
        html body input:focus, html body textarea:focus, html body select:focus, html body .form-control:focus {
            border-color:var(--mi-primary) !important; box-shadow:0 0 0 3px rgba(242,95,92,.14) !important;
        }
        html body .badge-success, html body .status-approved, html body .stock-ok, html body .rider-available {
            background:#dcfce7 !important; color:#15803d !important;
        }
        html body .badge-danger, html body .status-cancelled, html body .rider-unavailable {
            background:#fee2e2 !important; color:#b91c1c !important;
        }
    </style>

    <style>
        /* Global white admin theme. Important flags override legacy page-local dark styles. */
        html,
        html body,
        html body [class],
        html body [id] {
            background-color: #fff !important;
            background-image: none !important;
            color: #000 !important;
        }

        html body *,
        html body *::before,
        html body *::after {
            color: #000 !important;
        }

        :root {
            --mc-bg: #fff;
            --mc-shell: #fff;
            --mc-panel: #fff;
            --mc-panel-soft: #f8fafc;
            --mc-border: #e2e8f0;
            --mc-border-strong: #cbd5e1;
            --mc-text: #202124;
            --mc-muted: #64748b;
        }

        html,
        body,
        .header,
        .header .navbar,
        .header .container-fluid,
        nav#sidebar,
        nav#sidebar .sidebar-header,
        nav#sidebar ul ul,
        .page-content,
        .page-content.active,
        .page-header,
        .page-header .container-fluid,
        .admin-dashboard,
        .admin-surface,
        .admin-panel,
        .sales-card,
        .mini-card,
        .chart-panel,
        .table-wrap,
        .table-responsive,
        .admin-user-menu {
            background: #fff !important;
            background-image: none !important;
            color: #202124 !important;
        }

        nav#sidebar,
        .header .navbar {
            border-color: #e2e8f0 !important;
        }

        .admin-dashboard h1, .admin-dashboard h2, .admin-dashboard h3,
        .admin-surface h1, .admin-surface h2, .admin-surface h3,
        .admin-panel h1, .admin-panel h2, .admin-panel h3,
        .admin-brand, .admin-user-info strong,
        nav#sidebar .title h1,
        nav#sidebar a,
        label {
            color: #202124 !important;
        }

        nav#sidebar .title p,
        nav#sidebar .heading,
        .admin-user-info span {
            color: #64748b !important;
        }

        .admin-table th, .staff-table th, .users-table th,
        .inventory-table th, .orders-table th,
        .reservation-table th, .foods-table th,
        .admin-table td, .staff-table td, .users-table td,
        .inventory-table td, .orders-table td,
        .reservation-table td, .foods-table td {
            background: #fff !important;
            color: #202124 !important;
            border-color: #e2e8f0 !important;
        }

        .admin-table tr:hover td, .staff-table tr:hover td,
        .users-table tr:hover td, .inventory-table tr:hover td,
        .orders-table tr:hover td, .reservation-table tr:hover td,
        .foods-table tr:hover td,
        nav#sidebar li.active > a,
        nav#sidebar li a:hover {
            background: #f8fafc !important;
        }

        input, textarea, select, .form-control {
            background: #fff !important;
            color: #202124 !important;
            border-color: #cbd5e1 !important;
        }

        html body,
        html body .page-content,
        html body .page-header,
        html body .page-header > .container-fluid,
        html body .admin-dashboard,
        html body .admin-surface {
            background-color: #fff !important;
            background-image: none !important;
        }
    </style>


    <style>
        /* Light dashboard shell inspired by the supplied reference. */
        body {
            background: #050507;
            color: #fff;
            font-family: Muli, Arial, sans-serif;
        }

        .header {
            left: 230px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 900;
        }

        .header .navbar {
            background: #0b0b10;
            border-bottom: 1px solid #252530;
            min-height: 62px;
            padding: 7px 18px;
        }

        .header .container-fluid {
            background: transparent;
        }

        .admin-brand {
            color: #fff;
            font-size: 19px;
        }

        .admin-brand img {
            display: block;
            height: 38px;
            object-fit: contain;
            width: 38px;
        }

        .admin-user-trigger {
            background: #17171f;
            border: 2px solid #F88379;
            height: 42px;
            width: 42px;
        }

        nav#sidebar {
            align-items: stretch;
            background: #11111f;
            bottom: 0;
            display: block;
            flex-wrap: nowrap;
            gap: 0;
            left: 0;
            max-width: 230px !important;
            min-width: 230px !important;
            overflow-y: auto;
            padding: 16px 12px;
            position: fixed;
            top: 0;
            width: 230px !important;
            z-index: 1000;
        }

        nav#sidebar .sidebar-header {
            background: transparent;
            border: 0;
            display: flex !important;
            margin-bottom: 12px;
            padding: 8px 12px 14px;
        }

        nav#sidebar .sidebar-header .avatar {
            display: none;
        }

        nav#sidebar .sidebar-header .title {
            margin: 0;
        }

        nav#sidebar .sidebar-header .title h1 {
            color: #fff;
            font-size: 23px;
            font-weight: 900;
            letter-spacing: .2px;
            line-height: 1.2;
            margin: 0;
            text-transform: capitalize;
        }

        nav#sidebar .sidebar-header .title p {
            color: #FFA69E;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 7px 0 0;
            text-transform: uppercase;
        }

        nav#sidebar .heading {
            color: #737386;
            display: block !important;
            font-size: 11px;
            letter-spacing: 1.4px;
            margin: 0 14px 12px;
        }

        nav#sidebar > ul.list-unstyled {
            align-items: stretch;
            display: block !important;
            flex-wrap: nowrap;
            gap: 0;
            margin: 0;
        }

        nav#sidebar ul li {
            display: block;
            margin-bottom: 3px;
            position: relative;
            width: 100%;
        }

        nav#sidebar ul li > a {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 12px;
            color: #b9b9c8;
            display: flex;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .1px;
            line-height: 1.25;
            min-height: 46px;
            padding: 0 14px;
            width: 100%;
        }

        nav#sidebar ul li > a i {
            color: #aaaabb;
            flex: 0 0 24px;
            font-size: 18px;
            margin-right: 12px;
            min-width: 24px;
            text-align: center;
        }

        nav#sidebar li.active > a,
        nav#sidebar li a:hover,
        nav#sidebar .food-menu > a[aria-expanded="true"] {
            background: #242433;
            border: 0;
            color: #fff;
        }

        nav#sidebar li.active > a i,
        nav#sidebar li a:hover i {
            color: #F88379;
        }

        nav#sidebar ul ul {
            background: #191927;
            border: 0;
            margin: 7px 0 10px;
            padding: 7px;
            position: static;
        }

        .page-content,
        .page-content.active {
            background: #050507;
            margin-left: 230px;
            min-height: 100vh;
            padding-top: 62px;
            width: calc(100% - 230px) !important;
        }

        .page-header,
        .page-header .container-fluid {
            background: #050507;
        }

        .page-header {
            padding: 12px;
        }

        @media (max-width: 767px) {
            nav#sidebar {
                max-width: 230px !important;
                min-width: 230px !important;
                transform: translateX(-100%);
            }

            .header {
                left: 0;
            }

            .page-content,
            .page-content.active {
                margin-left: 0;
                width: 100% !important;
            }
        }
    </style>
