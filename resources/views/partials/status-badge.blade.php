@php
    $value = is_bool($status ?? null) ? (($status ?? false) ? 'active' : 'inactive') : strtolower((string) ($status ?? 'inactive'));
    $label = $label ?? match ($value) {
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'pending' => 'Pending',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'available' => 'Tersedia',
        'tersedia' => 'Tersedia',
        'used' => 'Terpakai',
        'terpakai' => 'Terpakai',
        default => str($value)->headline(),
    };
@endphp

<span class="status status-{{ $value }}">{{ $label }}</span>
