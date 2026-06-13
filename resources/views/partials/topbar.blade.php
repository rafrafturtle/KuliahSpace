<header class="topbar">
    <div class="topbar-title">
        <span class="topbar-title-icon material-symbols-rounded">dashboard_customize</span>
        <div>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="muted">@yield('subtitle', 'Manajemen ruang kuliah sederhana untuk kebutuhan akademik.')</div>
        </div>
    </div>

    <div class="topbar-note">
        <button id="themeToggle" class="btn btn-small btn-soft" type="button" title="Ganti tema">
            <span id="themeIcon" class="material-symbols-rounded" style="font-size: 18px;">dark_mode</span>
        </button>

        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="user-info">
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ auth()->user()->roles->pluck('title')->filter()->join(', ') ?: auth()->user()->roles->pluck('name')->join(', ') }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button class="btn btn-small btn-soft" type="submit">
                <span class="material-symbols-rounded" style="font-size: 18px;">logout</span>
                Logout
            </button>
        </form>
    </div>
</header>