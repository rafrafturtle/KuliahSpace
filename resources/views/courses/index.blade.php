@extends('layouts.app')

@section('title', 'Mata Kuliah')
@section('subtitle', 'Kelola kode, nama, dan bobot SKS mata kuliah.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('courses.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('courses.create') }}">Tambah Mata Kuliah</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>SKS</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td><strong>{{ $course->code }}</strong></td>
                        <td>{{ $course->name }}</td>
                        <td>{{ $course->credits ?? '-' }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small" href="{{ route('courses.show', $course) }}">Detail</a>
                                <a class="btn btn-small btn-soft" href="{{ route('courses.edit', $course) }}">Edit</a>
                                <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Hapus mata kuliah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">Belum ada data mata kuliah.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $courses->links() }}</div>
@endsection
