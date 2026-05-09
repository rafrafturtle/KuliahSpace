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
            ($isAdmin || $isDosen || $isKetuaKelas) ? ['label' => 'Permintaan Ruang', 'route' => 'room-requests.index', 'match' => 'room-requests.*', 'icon' => 'P'] : null,
        ]),
        'Akademik' => array_filter([
            ($isAdmin || $isDosen || $isKetuaKelas || $isMahasiswa) ? ['label' => 'Jadwal Kuliah', 'route' => 'schedules.index', 'match' => 'schedules.*', 'icon' => 'J'] : null,
            $isAdmin ? ['label' => 'Ruangan', 'route' => 'rooms.index', 'match' => 'rooms.*', 'icon' => 'R'] : null,
            $isAdmin ? ['label' => 'Mata Kuliah', 'route' => 'courses.index', 'match' => 'courses.*', 'icon' => 'M'] : null,
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
    <a href="{{ route('dashboard') }}" class="brand">
        <span class="brand-mark">KS</span>
        <span>KuliahSpace</span>
    </a>

    @foreach ($sections as $section => $items)
        @if (count($items) > 0)
            <div class="nav-section">{{ $section }}</div>
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                    <span class="nav-icon">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    @endforeach
</aside>
