@php
    $user = auth()->user();
    $isAdmin = $user?->isAn('admin') ?? false;
    $isDosen = $user?->isAn('dosen') ?? false;
    $isKetuaKelas = $user?->isAn('ketua_kelas') ?? false;
    $isMahasiswa = $user?->isAn('mahasiswa') ?? false;

    $sections = [
        'Utama' => array_filter([
            $isAdmin ? ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'space_dashboard'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas || $isMahasiswa) ? ['label' => 'Ketersediaan Ruang', 'route' => 'room-availability.index', 'match' => 'room-availability.*', 'icon' => 'meeting_room'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas || $isMahasiswa) ? ['label' => 'Pencarian Ruangan', 'route' => 'room-search.index', 'match' => 'room-search.*', 'icon' => 'manage_search'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas) ? ['label' => 'Permintaan Ruang', 'route' => 'room-requests.index', 'match' => 'room-requests.*', 'icon' => 'event_available'] : null,
            $isAdmin ? ['label' => 'Riwayat Pengajuan Ruang', 'route' => 'room-request-history.index', 'match' => 'room-request-history.*', 'icon' => 'history'] : null,
        ]),
        'Akademik' => array_filter([
            $isAdmin ? ['label' => 'Gedung', 'route' => 'buildings.index', 'match' => 'buildings.*', 'icon' => 'domain'] : null,
            $isAdmin ? ['label' => 'Ruangan', 'route' => 'rooms.index', 'match' => 'rooms.*', 'icon' => 'apartment'] : null,
            $isAdmin ? ['label' => 'Mata Kuliah', 'route' => 'courses.index', 'match' => 'courses.*', 'icon' => 'menu_book'] : null,
            $isAdmin ? ['label' => 'Jadwal Kuliah', 'route' => 'schedules.index', 'match' => 'schedules.*', 'icon' => 'calendar_month'] : null,
            $isAdmin ? ['label' => 'Semester', 'route' => 'semesters.index', 'match' => 'semesters.*', 'icon' => 'school'] : null,
            $isAdmin ? ['label' => 'Tahun Akademik', 'route' => 'academic-years.index', 'match' => 'academic-years.*', 'icon' => 'date_range'] : null,
        ]),
        'Pengguna' => array_filter([
            $isAdmin ? ['label' => 'Pengguna & Role', 'route' => 'users.index', 'match' => 'users.*', 'icon' => 'groups'] : null,
            ($isAdmin || $isDosen) ? ['label' => 'Ketua Kelas', 'route' => 'class-leaders.index', 'match' => 'class-leaders.*', 'icon' => 'verified_user'] : null,
        ]),
    ];
@endphp

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="brand" aria-label="KuliahSpace">
            <span class="brand-mark">
                <img src="{{ asset('images/LogoKuliahSpace.png') }}" alt="KuliahSpace">
            </span>
            <span class="brand-text">
                <strong>KuliahSpace</strong>
                <span>Smart Class Management</span>
            </span>
        </a>
        <button class="sidebar-toggle" type="button" aria-label="Minimize sidebar" aria-expanded="true" data-sidebar-toggle>
            <span class="material-symbols-rounded" aria-hidden="true" data-sidebar-toggle-icon>keyboard_double_arrow_left</span>
        </button>
    </div>

    @foreach ($sections as $section => $items)
        @if (count($items) > 0)
            <div class="nav-section">{{ $section }}</div>
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}" title="{{ $item['label'] }}">
                    <span class="nav-icon material-symbols-rounded">{{ $item['icon'] }}</span>
                    <span class="nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    @endforeach
    <div id="resizeHandle" class="resize-handle"></div>
</aside>
