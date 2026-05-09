@extends('layouts.app')

@section('title', 'Akses Ditolak')
@section('subtitle', 'Akun Anda tidak memiliki izin untuk membuka halaman ini.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <h2 style="margin-top: 0;">Akses tidak tersedia</h2>
            <p class="muted">Silakan gunakan menu yang tersedia untuk role akun Anda.</p>
            <div class="actions" style="margin-top: 18px;">
                <a class="btn btn-primary" href="{{ auth()->user()?->isAn('admin') ? route('dashboard') : route('schedules.index') }}">Kembali</a>
            </div>
        </div>
    </div>
@endsection
