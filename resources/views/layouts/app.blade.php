<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - KuliahSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,600,0,0" rel="stylesheet">
    <script>
        try {
            if (localStorage.getItem('kuliahspace.sidebar') === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }

            if (localStorage.getItem('kuliahspace.theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {}
    </script>
    <style>
        :root {
            --bg: #eef6ff;
            --surface: rgba(255, 255, 255, .92);
            --surface-solid: #ffffff;
            --surface-muted: #f4f8ff;
            --line: #d9e7f8;
            --text: #0b2a68;
            --muted: #557096;
            --blue: #0b60eb;
            --blue-2: #0b55bd;
            --navy: #05266d;
            --sky: #8cc6ff;
            --soft-blue: #e8f3ff;
            --soft-blue-2: #dcecff;
            --green: #0f8f65;
            --green-soft: #e8f8f1;
            --amber: #b77905;
            --amber-soft: #fff7df;
            --red: #d93b52;
            --red-soft: #fff0f3;
            --shadow: 0 24px 70px rgba(5, 38, 109, .12);
            --shadow-soft: 0 14px 35px rgba(5, 38, 109, .08);
            --radius: 24px;
            --radius-sm: 16px;
            --sidebar-width: 286px;
        }

        html.dark {
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, .92);
            --surface-solid: #1e293b;
            --surface-muted: #334155;
            --line: #475569;
            --text: #f8fafc;
            --muted: #94a3b8;
            --soft-blue: #172554;
            --soft-blue-2: #1e3a8a;
            --green-soft: rgba(15, 143, 101, .18);
            --amber-soft: rgba(183, 121, 5, .18);
            --red-soft: rgba(217, 59, 82, .18);
            --shadow: 0 24px 70px rgba(0, 0, 0, .35);
            --shadow-soft: 0 14px 35px rgba(0, 0, 0, .25);
        }

        html.dark body {
            background:
                radial-gradient(circle at 12% 8%, rgba(59, 130, 246, .20), transparent 28%),
                radial-gradient(circle at 92% 12%, rgba(37, 99, 235, .18), transparent 30%),
                linear-gradient(135deg, #020617 0%, #0f172a 55%, #111827 100%);
        }

        html.dark body::before {
            background-image:
                linear-gradient(rgba(148, 163, 184, .06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, .06) 1px, transparent 1px);
        }

        html.dark .topbar,
        html.dark .panel,
        html.dark .bento-card,
        html.dark .room-card {
            background: var(--surface);
            border-color: var(--line);
        }

        html.dark .panel-header {
            background: linear-gradient(180deg, #1e293b, #0f172a);
            border-bottom-color: var(--line);
        }

        html.dark .topbar-note,
        html.dark .empty-state {
            background: #1e293b;
            border-color: #475569;
        }

        html.dark th {
            background: #1e293b;
            color: #cbd5e1;
        }

        html.dark td {
            color: #e2e8f0;
        }

        html.dark tbody tr:hover {
            background: rgba(51, 65, 85, .65);
        }

        html.dark input,
        html.dark select,
        html.dark textarea,
        html.dark .btn {
            background: #1e293b;
            border-color: #475569;
            color: #f8fafc;
        }

        html.dark .btn-soft {
            background: #172554;
            border-color: #1d4ed8;
            color: #bfdbfe;
        }

        html.dark label {
            color: #e2e8f0;
        }

        html.dark .room-card {
            background: linear-gradient(180deg, #1e293b, #0f172a);
        }

        html.dark .status-inactive,
        html.dark .status-cancelled {
            background: rgba(100, 116, 139, .22);
            color: #cbd5e1;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Inter", "Segoe UI", system-ui, sans-serif;
            font-size: 15px;
            line-height: 1.55;
            background:
                radial-gradient(circle at 12% 8%, rgba(140, 198, 255, .45), transparent 28%),
                radial-gradient(circle at 92% 12%, rgba(11, 96, 235, .18), transparent 30%),
                linear-gradient(135deg, #eef6ff 0%, #fbfdff 55%, #edf6ff 100%);
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(11, 96, 235, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(11, 96, 235, .045) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,.7), transparent 72%);
            z-index: -1;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: normal;
            font-style: normal;
            font-size: 22px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }

        .app-shell {
            display: grid;
            grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
            min-height: 100vh;
            transition: grid-template-columns .22s ease;
        }
        .sidebar-collapsed .app-shell { grid-template-columns: 86px minmax(0, 1fr); }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 22px 16px;
            color: #eaf4ff;
            background:
                radial-gradient(circle at 20% 0%, rgba(140, 198, 255, .24), transparent 35%),
                linear-gradient(160deg, #05266d 0%, #0b55bd 56%, #083d91 100%);
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 18px 0 55px rgba(5, 38, 109, .22);
            transition: padding .22s ease;
            z-index: 10;
        }
        .sidebar::before,
        .sidebar::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            pointer-events: none;
        }
        .sidebar::before { width: 230px; height: 230px; right: -120px; top: 95px; }
        .sidebar::after { width: 170px; height: 170px; left: -90px; bottom: 70px; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.22); border-radius: 999px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-collapsed .sidebar { padding: 18px 12px; }

        .resize-handle {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 100%;
            cursor: ew-resize;
            background: transparent;
            z-index: 4;
        }
        .resize-handle:hover { background: rgba(255,255,255,.12); }

        .sidebar-header {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 2px 20px;
            margin-bottom: 4px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            font-weight: 900;
        }
        .brand-mark {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 17px;
            overflow: hidden;
        }
        .brand-mark img { width: 47px; height: 47px; object-fit: contain; }
        .brand-text { display: grid; line-height: 1.1; }
        .brand-text strong { font-size: 22px; letter-spacing: -.8px; color: #fff; }
        .brand-text span { font-size: 11px; font-weight: 700; color: rgba(234, 244, 255, .78); }
        .brand-text, .nav-label, .nav-section { transition: opacity .18s ease, width .18s ease; white-space: nowrap; }

        .sidebar-toggle {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 15px;
            background: rgba(255,255,255,.12);
            color: #fff;
            cursor: pointer;
            backdrop-filter: blur(12px);
        }
        .sidebar-toggle:hover { background: rgba(255,255,255,.2); transform: translateY(-1px); }
        .sidebar-collapsed .sidebar-header { flex-direction: column; justify-content: center; }
        .sidebar-collapsed .brand { justify-content: center; gap: 0; }
        .sidebar-collapsed .brand-text,
        .sidebar-collapsed .nav-label,
        .sidebar-collapsed .nav-section { width: 0; opacity: 0; overflow: hidden; }
        .sidebar-collapsed .nav-section { height: 0; margin: 8px 0; padding: 0; }
        .toggle-mobile{ display:none; }

        .nav-section {
            position: relative;
            z-index: 2;
            margin: 19px 0 9px;
            padding: 0 12px;
            color: rgba(234,244,255,.62);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .9px;
            text-transform: uppercase;
        }
        .nav-link {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 18px;
            color: rgba(234,244,255,.82);
            margin-bottom: 7px;
            transition: .18s ease;
        }
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,.16);
            color: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.13);
        }
        .nav-link.active::before {
            content: "";
            position: absolute;
            left: -5px;
            top: 50%;
            width: 4px;
            height: 30px;
            border-radius: 999px;
            background: #ffffff;
            transform: translateY(-50%);
        }
        .nav-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 14px;
            background: rgba(255,255,255,.13);
            color: #ffffff;
        }
        .nav-link.active .nav-icon,
        .nav-link:hover .nav-icon { background: #ffffff; color: var(--blue); }
        .nav-label { min-width: 0; overflow: hidden; text-overflow: ellipsis; font-weight: 750; font-size: 14px; }
        .sidebar-collapsed .nav-link { justify-content: center; gap: 0; padding: 10px; }
        .sidebar-collapsed .nav-icon { width: 42px; height: 42px; }

        .main-shell { min-width: 0; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 16px 20px 0;
            padding: 18px 20px;
            border: 1px solid rgba(217,231,248,.9);
            border-radius: 26px;
            background: rgba(255,255,255,.82);
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow-soft);
        }
        .topbar-title { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .topbar-title-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 18px;
            background: linear-gradient(145deg, var(--blue), var(--blue-2));
            color: #ffffff;
            box-shadow: 0 15px 35px rgba(11, 96, 235, .28);
        }
        .topbar h1 {
            margin: 0;
            font-size: clamp(22px, 2.4vw, 31px);
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: -1px;
            color: var(--text);
        }
        .topbar .muted { margin-top: 5px; }
        .topbar-note {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 0 0 auto;
            padding: 8px;
            border-radius: 20px;
            background: #f3f8ff;
            border: 1px solid #dcecff;
        }
        .user-avatar {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: linear-gradient(145deg, #0b60eb, #58adff);
            color: #fff;
            font-weight: 900;
            box-shadow: 0 10px 24px rgba(11,96,235,.22);
        }
        .user-info { min-width: 0; text-align: right; }
        .user-info strong { display: block; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; }
        .user-info span { display: block; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--muted); font-size: 12px; font-weight: 650; }
        .logout-form { margin: 0; }
        .content { padding: 22px 24px 44px; }

        .grid { display: grid; gap: 18px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .bento-card,
        .panel {
            position: relative;
            background: var(--surface);
            border: 1px solid rgba(217,231,248,.95);
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }
        .bento-card::before,
        .panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--blue), var(--sky));
            opacity: .95;
        }
        .bento-card { padding: 22px; transition: .18s ease; }
        .bento-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
        .bento-card h2,
        .panel-header h2 {
            margin: 0;
            font-size: 17px;
            line-height: 1.25;
            font-weight: 850;
            letter-spacing: -.25px;
        }
        .metric {
            margin-top: 14px;
            font-size: clamp(35px, 4vw, 48px);
            line-height: .95;
            font-weight: 900;
            letter-spacing: -1.5px;
            color: var(--blue);
        }
        .muted { color: var(--muted); }
        .stack { display: grid; gap: 15px; }
        .actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .panel { overflow: hidden; }
        .panel-header,
        .panel-body { padding: 19px 22px; }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, #ffffff, #f7fbff);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 15px;
            border: 1px solid #cfe0f4;
            border-radius: 15px;
            background: #ffffff;
            color: var(--text);
            cursor: pointer;
            font-weight: 800;
            transition: .18s ease;
            box-shadow: 0 8px 20px rgba(5, 38, 109, .05);
        }
        .btn:hover { transform: translateY(-1px); border-color: #a9c9f3; box-shadow: 0 12px 28px rgba(5, 38, 109, .09); }
        .btn-primary { background: linear-gradient(135deg, var(--blue), var(--blue-2)); border-color: transparent; color: #fff; box-shadow: 0 14px 30px rgba(11,96,235,.23); }
        .btn-soft { background: var(--soft-blue); border-color: #cfe3ff; color: var(--blue); }
        .btn-danger { background: var(--red-soft); border-color: #ffd1da; color: var(--red); }
        .btn-small { min-height: 34px; padding: 7px 11px; border-radius: 12px; font-size: 13px; }

        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 16px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: middle; }
        th { color: #5f789e; font-size: 11px; text-transform: uppercase; letter-spacing: .8px; background: #f4f8ff; font-weight: 900; }
        td { color: #1c376e; font-weight: 550; }
        tbody tr { transition: .16s ease; }
        tbody tr:hover { background: #f8fbff; }
        tr:last-child td { border-bottom: 0; }

        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; }
        .form-field { display: grid; gap: 8px; }
        .form-field.full { grid-column: 1 / -1; }
        label { font-weight: 800; color: #123372; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], input[type="time"], select, textarea {
            width: 100%;
            border: 1px solid #cfe0f4;
            border-radius: 16px;
            background: #ffffff;
            padding: 12px 14px;
            color: var(--text);
            outline: none;
            transition: .18s ease;
            box-shadow: 0 8px 18px rgba(5, 38, 109, .035);
        }
        input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(11, 96, 235, .11); }
        textarea { min-height: 120px; resize: vertical; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; min-height: 44px; }
        .field-error { color: var(--red); font-size: 13px; font-weight: 700; }
        .search-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .search-form input, .search-form select { min-width: 230px; }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 28px;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            text-transform: capitalize;
        }
        .status::before { content: ""; width: 7px; height: 7px; border-radius: 999px; background: currentColor; }
        .status-active, .status-approved, .status-available, .status-tersedia { background: var(--green-soft); color: var(--green); }
        .status-used, .status-terpakai { background: var(--soft-blue); color: var(--blue); }
        .status-pending { background: var(--amber-soft); color: var(--amber); }
        .status-inactive, .status-cancelled { background: #eef2f7; color: #64748b; }
        .status-rejected { background: var(--red-soft); color: var(--red); }

        .flash, .error-box, .empty-state {
            border-radius: 20px;
            padding: 15px 17px;
            margin-bottom: 18px;
            font-weight: 650;
        }
        .flash { background: var(--green-soft); border: 1px solid #bcebd9; color: var(--green); }
        .error-box { background: var(--red-soft); border: 1px solid #ffd1da; color: var(--red); }
        .empty-state { background: #f6faff; border: 1px dashed #bcd4f1; color: var(--muted); }
        .pagination { margin-top: 18px; }
        .pagination svg {
            width: 20px;
            height: 20px;
            max-width: 20px;
            max-height: 20px;
            flex-shrink: 0;
        }
        .pagination nav {
            display: grid;
            gap: 10px;
        }
        .pagination nav > div {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .pagination a,
        .pagination span {
            line-height: 1.25;
        }
        .detail-list { display: grid; grid-template-columns: 180px minmax(0, 1fr); gap: 12px 18px; }
        .detail-list dt { color: var(--muted); font-weight: 750; }
        .detail-list dd { margin: 0; font-weight: 800; }
        .day-section { margin-bottom: 20px; }
        .day-summary { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; color: var(--muted); font-size: 13px; font-weight: 650; }
        .room-card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; }
        .room-card {
            display: grid;
            gap: 9px;
            padding: 17px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            box-shadow: 0 10px 24px rgba(5, 38, 109, .06);
        }
        .room-card h3 { margin: 0; font-size: 17px; font-weight: 900; letter-spacing: -.3px; }
        .room-card-meta { display: grid; gap: 5px; color: var(--muted); font-size: 13px; font-weight: 650; }
        .room-photo-panel { padding: 12px; margin-bottom: 18px; overflow: hidden; }
        .room-photo {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface-muted);
            box-shadow: 0 14px 30px rgba(5, 38, 109, .08);
        }

        @media (max-width: 1100px) { .grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 980px) {
            .app-shell,
            .sidebar-collapsed .app-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                height: auto;
                max-height: 1000px;
                overflow: hidden;
                border-radius: 0 0 28px 28px;
                transition: max-height .35s ease, padding .25s ease;
            }

            .resize-handle {
                display: none;
            }

            .sidebar-collapsed .sidebar {
                max-height: 95px;
                padding: 18px;
            }

            .sidebar-collapsed .nav-section,
            .sidebar-collapsed .nav-link {
                display: none;
            }

            .sidebar-collapsed .brand-text {
                width: auto;
                opacity: 1;
                overflow: visible;
            }

            .sidebar-collapsed .sidebar-header {
                flex-direction: row;
                justify-content: space-between;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .sidebar-collapsed .brand {
                justify-content: flex-start;
                gap: 12px;
            }

            .grid-3,
            .grid-2,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                margin: 14px 14px 0;
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-note {
                width: 100%;
                justify-content: space-between;
            }

            .content {
                padding: 18px 14px 36px;
            }

            .detail-list {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .sidebar-toggle:hover {
                transform: translateY(-2px);
            }

            .sidebar-collapsed .sidebar-toggle:hover {
                transform: translateY(2px);
            }
        }
        @media (max-width: 620px) {
            .grid-4 { grid-template-columns: 1fr; }
            .topbar-title-icon { width: 46px; height: 46px; border-radius: 16px; }
            .topbar { position: relative; }
            .topbar-note { align-items: flex-start; flex-wrap: wrap; }
            .user-info { text-align: left; }
            .panel-header { align-items: flex-start; flex-direction: column; }
            th, td { padding: 12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    @include('partials.sidebar')
    <main class="main-shell">
        @include('partials.topbar')
        <section class="content">
            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="error-box">
                    <strong>Periksa kembali input.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>
@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.documentElement;
    const handle = document.getElementById('resizeHandle');
    let isResizing = false;

    if (handle) {
        handle.addEventListener('mousedown', function () {
            isResizing = true;
            document.body.style.cursor = 'ew-resize';
        });

        document.addEventListener('mousemove', function (e) {
            if (!isResizing) return;
            let width = e.clientX;
            if (width >= 86 && width <= 450) {
                root.style.setProperty('--sidebar-width', width + 'px');
                if (width <= 126) {
                    root.classList.add('sidebar-collapsed');
                } else {
                    root.classList.remove('sidebar-collapsed');
                }
            }
        });

        document.addEventListener('mouseup', function () {
            isResizing = false;
            document.body.style.cursor = '';
        });
    }

    const topbarNote = document.querySelector('.topbar-note');

    if (topbarNote && !document.getElementById('themeToggle')) {
        const themeButton = document.createElement('button');
        themeButton.id = 'themeToggle';
        themeButton.className = 'btn btn-small btn-soft';
        themeButton.type = 'button';
        themeButton.title = 'Ganti tema';
        themeButton.innerHTML = '<span id="themeIcon" class="material-symbols-rounded" style="font-size: 18px;">dark_mode</span>';
        topbarNote.prepend(themeButton);
    }

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    function setTheme(theme) {
        root.classList.toggle('dark', theme === 'dark');

        if (themeIcon) {
            themeIcon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
        }

        try {
            localStorage.setItem('kuliahspace.theme', theme);
        } catch (error) {}
    }

    setTheme(localStorage.getItem('kuliahspace.theme') || 'light');

    themeToggle?.addEventListener('click', function () {
        setTheme(root.classList.contains('dark') ? 'light' : 'dark');
    });

    var toggle = document.querySelector('[data-sidebar-toggle]');
    var toggleIcon = document.querySelector('[data-sidebar-toggle-icon]');
    if (!toggle) return;

    function setCollapsed(collapsed) {
        const isMobile = window.innerWidth <= 768;

        root.classList.toggle('sidebar-collapsed', collapsed);
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
        toggle.setAttribute('title', collapsed ? 'Buka sidebar' : 'Tutup sidebar');

        if (toggleIcon) {
            if (isMobile) {
                toggleIcon.textContent = collapsed ? 'keyboard_double_arrow_down' : 'keyboard_double_arrow_up';
            } else {
                toggleIcon.textContent = collapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
            }
        }

        try {
            localStorage.setItem('kuliahspace.sidebar', collapsed ? 'collapsed' : 'expanded');
        } catch (error) {}
    }

    setCollapsed(root.classList.contains('sidebar-collapsed'));
    toggle.addEventListener('click', function () {
        setCollapsed(!root.classList.contains('sidebar-collapsed'));
    });
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 980) {
                setCollapsed(true);
            }
        });
    });
    window.addEventListener('resize', function () {
        setCollapsed(root.classList.contains('sidebar-collapsed'));
    });
});
</script>
</body>
</html>
