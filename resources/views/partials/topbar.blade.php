<header class="topbar">
    <div>
        <h1>@yield('title', 'Dashboard')</h1>
        <div class="muted">@yield('subtitle', 'Manajemen ruang kuliah sederhana untuk kebutuhan akademik.')</div>
    </div>
    <div class="topbar-note">
        <strong>{{ auth()->user()->name }}</strong><br>
        <span>{{ auth()->user()->roles->pluck('title')->filter()->join(', ') ?: auth()->user()->roles->pluck('name')->join(', ') }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 8px;">
            @csrf
            <button class="btn btn-small" type="submit">Logout</button>
        </form>
    </div>
</header>
