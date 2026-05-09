<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - KuliahSpace</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --surface: #ffffff;
            --line: #dbe3ea;
            --text: #172033;
            --muted: #667085;
            --blue-soft: #e9f3fb;
            --red: #b13a3a;
            --red-soft: #fff0f0;
            --sage-soft: #edf7f0;
            --shadow: 0 18px 50px rgba(23, 32, 51, .08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 15px;
            line-height: 1.5;
            padding: 24px;
        }
        .login-shell { width: min(100%, 430px); }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 18px;
            font-size: 22px;
            font-weight: 800;
        }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #dff2e6;
            color: #174331;
            font-weight: 900;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 24px;
        }
        h1 { margin: 0; font-size: 24px; letter-spacing: 0; }
        p { margin: 6px 0 0; color: var(--muted); }
        form { display: grid; gap: 16px; margin-top: 22px; }
        label { display: grid; gap: 7px; font-weight: 700; color: #27364a; }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            padding: 10px 12px;
            color: var(--text);
            font: inherit;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--muted);
            font-weight: 650;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            border: 1px solid #173f5f;
            border-radius: 8px;
            background: #173f5f;
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 750;
        }
        .error-box,
        .flash {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }
        .error-box { background: var(--red-soft); border: 1px solid #f3caca; color: var(--red); }
        .flash { background: var(--sage-soft); border: 1px solid #cdebd5; color: #276342; }
        .demo {
            margin-top: 16px;
            padding: 14px;
            border-radius: 8px;
            background: var(--blue-soft);
            color: #194f75;
            font-size: 13px;
        }
        .demo code { font-weight: 800; }
    </style>
</head>
<body>
<main class="login-shell">
    <div class="brand">
        <span class="brand-mark">KS</span>
        <span>KuliahSpace</span>
    </div>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="card">
        <h1>Masuk</h1>
        <p>Pilih akun demo sesuai role untuk mensimulasikan akses.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <label class="checkbox-row">
                <input type="checkbox" name="remember" value="1">
                Ingat sesi
            </label>

            <button class="btn" type="submit">Login</button>
        </form>

        <div class="demo">
            Admin: <code>admin@ulm.ac.id</code><br>
            Dosen: <code>dosen@ulm.ac.id</code><br>
            Ketua kelas: <code>ketuakelas@ulm.ac.id</code><br>
            Mahasiswa: <code>mahasiswa@ulm.ac.id</code><br>
            Password semua akun: <code>password</code>
        </div>
    </section>
</main>
</body>
</html>
