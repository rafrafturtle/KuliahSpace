@php
    $user = auth()->user();
    $isAdmin = $user?->isAn('admin') ?? false;
    $isDosen = $user?->isAn('dosen') ?? false;
    $isKetuaKelas = $user?->isAn('ketua_kelas') ?? false;
    $isMahasiswa = $user?->isAn('mahasiswa') ?? false;

    $sections = [
        'Utama' => array_filter([
            $isAdmin ? ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'D'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas || $isMahasiswa) ? ['label' => 'Ketersediaan Ruang', 'route' => 'room-availability.index', 'match' => 'room-availability.*', 'icon' => 'K'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas || $isMahasiswa) ? ['label' => 'Pencarian Ruangan', 'route' => 'room-search.index', 'match' => 'room-search.*', 'icon' => 'C'] : null,
            ($isAdmin || $isDosen || $isKetuaKelas) ? ['label' => 'Permintaan Ruang', 'route' => 'room-requests.index', 'match' => 'room-requests.*', 'icon' => 'P'] : null,
            $isAdmin ? ['label' => 'Riwayat Pengajuan Ruang', 'route' => 'room-request-history.index', 'match' => 'room-request-history.*', 'icon' => 'H'] : null,
        ]),
        'Akademik' => array_filter([
            $isAdmin ? ['label' => 'Ruangan', 'route' => 'rooms.index', 'match' => 'rooms.*', 'icon' => 'R'] : null,
            $isAdmin ? ['label' => 'Mata Kuliah', 'route' => 'courses.index', 'match' => 'courses.*', 'icon' => 'M'] : null,
            $isAdmin ? ['label' => 'Jadwal Kuliah', 'route' => 'schedules.index', 'match' => 'schedules.*', 'icon' => 'J'] : null,
            $isAdmin ? ['label' => 'Semester', 'route' => 'semesters.index', 'match' => 'semesters.*', 'icon' => 'S'] : null,
            $isAdmin ? ['label' => 'Tahun Akademik', 'route' => 'academic-years.index', 'match' => 'academic-years.*', 'icon' => 'T'] : null,
        ]),
        'Pengguna' => array_filter([
            $isAdmin ? ['label' => 'Pengguna & Role', 'route' => 'users.index', 'match' => 'users.*', 'icon' => 'U'] : null,
            ($isAdmin || $isDosen) ? ['label' => 'Ketua Kelas', 'route' => 'class-leaders.index', 'match' => 'class-leaders.*', 'icon' => 'K'] : null,
        ]),
    ];
@endphp

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="brand" aria-label="KuliahSpace">
            <span class="brand-mark">KS</span>
            <span class="brand-text">KuliahSpace</span>
        </a>
        <button class="sidebar-toggle" type="button" aria-label="Minimize sidebar" aria-expanded="true" data-sidebar-toggle>
            <span aria-hidden="true" data-sidebar-toggle-icon>&lt;&lt;</span>
        </button>
    </div>

    @foreach ($sections as $section => $items)
        @if (count($items) > 0)
            <div class="nav-section">{{ $section }}</div>
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}" title="{{ $item['label'] }}">
                    <span class="nav-icon">{{ $item['icon'] }}</span>
                    <span class="nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    @endforeach
</aside>
