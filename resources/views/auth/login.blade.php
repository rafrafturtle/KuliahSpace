<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - KuliahSpace</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400;500;600;700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<main class="login-page">
    <section class="left-panel">
        <div class="brand">
            <img src="{{ asset('images/LogoKuliahSpace.png') }}" alt="KuliahSpace">
            <div>
                <h1>Kuliah<span>Space</span></h1>
                <p>Kelola Kelas. Jadwal. Aktivitas.<br><b>Semua dalam Satu Ruang.</b></p>
            </div>
        </div>

        <div class="hero">
            <div class="hero-line"></div>
            <h2>Manajemen Kelas<br>Lebih Mudah,<br>Terstruktur, dan Efisien.</h2>
            <p>
                KuliahSpace membantu dosen, ketua kelas, PJ, dan mahasiswa
                mengelola kelas, jadwal, tugas, ruang, serta informasi akademik
                dengan lebih terorganisir.
            </p>
        </div>

        <div class="features">
            <div class="feature">
                <span class="material-symbols-rounded">calendar_month</span>
                <h3>Manajemen Jadwal</h3>
                <p>Atur jadwal kelas dengan mudah dan fleksibel.</p>
            </div>
            <div class="feature">
                <span class="material-symbols-rounded">meeting_room</span>
                <h3>Penggunaan Ruang</h3>
                <p>Kelola pemakaian ruang kelas secara terstruktur dan transparan.</p>
            </div>
            <div class="feature">
                <span class="material-symbols-rounded">sync_alt</span>
                <h3>Kelas Pengganti</h3>
                <p>Kelola kelas pengganti dengan cepat dan praktis.</p>
            </div>
            <div class="feature">
                <span class="material-symbols-rounded">article</span>
                <h3>Informasi Terpusat</h3>
                <p>Semua informasi kelas tersimpan rapi dalam satu tempat.</p>
            </div>
        </div>

        <div class="secure-card">
            <div class="secure-icon">
                <span class="material-symbols-rounded">verified_user</span>
            </div>
            <div>
                <h3>Aman • Terpercaya • Terintegrasi</h3>
                <p>KuliahSpace dirancang khusus untuk mendukung proses akademik yang lebih baik.</p>
            </div>
            <img src="{{ asset('images/LogoKuliahSpace.png') }}" alt="KuliahSpace">
        </div>
    </section>

    <section class="right-panel">
        <div class="login-card">
            <img class="login-logo" src="{{ asset('images/LogoKuliahSpace.png') }}" alt="KuliahSpace">

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

            <div class="login-title">
                <h2>Selamat Datang!</h2>
                <p>Masuk ke akun Anda untuk melanjutkan ke KuliahSpace.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <label>Email</label>
                <div class="input-group">
                    <span class="material-symbols-rounded">mail</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus>
                </div>

                <label>Password</label>
                <div class="input-group">
                    <span class="material-symbols-rounded">lock</span>
                    <input type="password" name="password" placeholder="Masukkan password Anda" required>
                </div>

                <div class="form-row">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        Ingat saya
                    </label>
                    <a href="#">Lupa password?</a>
                </div>

                <button type="submit">Masuk</button>
            </form>

            <div class="divider">
                <span></span>
                <p>atau masuk sebagai</p>
                <span></span>
            </div>

            <div class="demo-box">
                <div class="demo-title">
                    <div class="demo-icon">
                        <span class="material-symbols-rounded">account_circle</span>
                    </div>
                    <h3>Akun<br>Demo</h3>
                </div>

                <div class="demo-list">
                    <p><b>Admin</b><span>:</span><span>admin@ulm.ac.id</span></p>
                    <p><b>Dosen</b><span>:</span><span>dosen@ulm.ac.id</span></p>
                    <p><b>Ketua kelas</b><span>:</span><span>ketuakelas@ulm.ac.id</span></p>
                    <p><b>Mahasiswa</b><span>:</span><span>mahasiswa@ulm.ac.id</span></p>
                    <p><b>Password</b><span>:</span><span>password</span></p>
                </div>
            </div>

            <p class="safe-text">
                <span class="material-symbols-rounded">verified_user</span>
                Data Anda aman bersama KuliahSpace.
            </p>
        </div>
    </section>
</main>
</body>
</html>