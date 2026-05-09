<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - KuliahSpace</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --surface: #ffffff;
            --surface-muted: #f8fafc;
            --line: #dbe3ea;
            --text: #172033;
            --muted: #667085;
            --blue: #3b82b6;
            --blue-soft: #e9f3fb;
            --sage: #5f8f73;
            --sage-soft: #edf7f0;
            --amber: #a96916;
            --amber-soft: #fff6df;
            --red: #b13a3a;
            --red-soft: #fff0f0;
            --shadow: 0 18px 50px rgba(23, 32, 51, .06);
            --radius: 8px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 15px;
            line-height: 1.5;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }

        .app-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }
        .sidebar {
            background: #111827;
            color: #e5edf6;
            padding: 22px 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px 22px;
            font-weight: 800;
            font-size: 20px;
        }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #dff2e6;
            color: #174331;
            font-weight: 900;
        }
        .nav-section {
            margin: 18px 0 8px;
            padding: 0 10px;
            color: #9fb0c3;
            font-size: 11px;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #cbd5e1;
            margin-bottom: 4px;
        }
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
        }
        .nav-icon {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            border-radius: 7px;
            background: rgba(255, 255, 255, .08);
            font-size: 12px;
            font-weight: 800;
        }

        .main-shell { min-width: 0; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 30px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .topbar h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1.2;
            letter-spacing: 0;
        }
        .topbar-note { color: var(--muted); font-size: 13px; text-align: right; }
        .content { padding: 28px 30px 40px; }

        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .bento-card,
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .bento-card { padding: 20px; }
        .panel { overflow: hidden; }
        .panel-header,
        .panel-body {
            padding: 18px 20px;
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--line);
        }
        .panel-header h2,
        .bento-card h2 {
            margin: 0;
            font-size: 17px;
            letter-spacing: 0;
        }
        .metric {
            margin-top: 14px;
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0;
        }
        .muted { color: var(--muted); }
        .stack { display: grid; gap: 14px; }
        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 13px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            font-weight: 650;
        }
        .btn:hover { border-color: #afbfce; }
        .btn-primary { background: #173f5f; border-color: #173f5f; color: #fff; }
        .btn-soft { background: var(--blue-soft); border-color: #cae0f3; color: #194f75; }
        .btn-danger { background: var(--red-soft); border-color: #f3caca; color: var(--red); }
        .btn-small { min-height: 32px; padding: 6px 10px; font-size: 13px; }

        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 13px 14px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0; background: var(--surface-muted); }
        tr:last-child td { border-bottom: 0; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .form-field { display: grid; gap: 7px; }
        .form-field.full { grid-column: 1 / -1; }
        label { font-weight: 700; color: #27364a; }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            padding: 10px 12px;
            color: var(--text);
        }
        textarea { min-height: 110px; resize: vertical; }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
        }
        .field-error { color: var(--red); font-size: 13px; }
        .search-form { display: flex; gap: 8px; flex-wrap: wrap; }
        .search-form input,
        .search-form select { min-width: 220px; }

        .status {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: capitalize;
        }
        .status-active,
        .status-approved { background: var(--sage-soft); color: #296246; }
        .status-available,
        .status-tersedia { background: var(--sage-soft); color: #296246; }
        .status-used,
        .status-terpakai { background: var(--blue-soft); color: #194f75; }
        .status-pending { background: var(--amber-soft); color: var(--amber); }
        .status-inactive,
        .status-cancelled { background: #eef2f7; color: #536172; }
        .status-rejected { background: var(--red-soft); color: var(--red); }

        .flash,
        .error-box,
        .empty-state {
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .flash { background: var(--sage-soft); border: 1px solid #cdebd5; color: #276342; }
        .error-box { background: var(--red-soft); border: 1px solid #f3caca; color: var(--red); }
        .empty-state { background: var(--surface-muted); border: 1px dashed var(--line); color: var(--muted); }
        .pagination { margin-top: 16px; }
        .detail-list { display: grid; grid-template-columns: 170px minmax(0, 1fr); gap: 10px 16px; }
        .detail-list dt { color: var(--muted); }
        .detail-list dd { margin: 0; font-weight: 650; }
        .day-section { margin-bottom: 18px; }
        .day-summary {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 13px;
        }
        .room-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
        }
        .room-card {
            display: grid;
            gap: 8px;
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
        }
        .room-card h3 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 0;
        }
        .room-card-meta {
            display: grid;
            gap: 4px;
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 980px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .grid-4, .grid-3, .grid-2, .form-grid { grid-template-columns: 1fr; }
            .content, .topbar { padding-left: 18px; padding-right: 18px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .topbar-note { text-align: left; }
            .detail-list { grid-template-columns: 1fr; }
        }
    </style>
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
</body>
</html>
