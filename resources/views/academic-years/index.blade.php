@extends('layouts.app')

@section('title', 'Tahun Akademik')
@section('subtitle', 'Kelola periode akademik aktif.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('academic-years.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari tahun akademik">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('academic-years.create') }}">Tambah Tahun</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($academicYears as $academicYear)
                    <tr>
                        <td><strong>{{ $academicYear->name }}</strong></td>
                        <td>@include('partials.status-badge', ['status' => $academicYear->is_active])</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small btn-soft" href="{{ route('academic-years.edit', $academicYear) }}">Edit</a>
                                <form method="POST" action="{{ route('academic-years.destroy', $academicYear) }}" onsubmit="return confirm('Hapus tahun akademik ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty-state">Belum ada data tahun akademik.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $academicYears->links() }}</div>
@endsection
