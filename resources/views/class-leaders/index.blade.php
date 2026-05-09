@extends('layouts.app')

@section('title', 'Ketua Kelas Mata Kuliah')
@section('subtitle', 'Tetapkan satu mahasiswa sebagai ketua kelas untuk kombinasi mata kuliah, dosen, semester, dan tahun akademik.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('class-leaders.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari mahasiswa, dosen, mata kuliah">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('class-leaders.create') }}">Tetapkan Ketua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Dosen</th>
                    <th>Mata Kuliah</th>
                    <th>Periode</th>
                    <th>Ditugaskan</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($classLeaders as $classLeader)
                    <tr>
                        <td><strong>{{ $classLeader->student?->name }}</strong></td>
                        <td>{{ $classLeader->lecturer?->name }}</td>
                        <td>{{ $classLeader->course?->code }} - {{ $classLeader->course?->name }}</td>
                        <td>{{ $classLeader->semester?->name }}<br>{{ $classLeader->academicYear?->name }}</td>
                        <td>{{ $classLeader->assigned_at->format('d M Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('class-leaders.destroy', $classLeader) }}" onsubmit="return confirm('Hapus penetapan ketua kelas ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Belum ada ketua kelas mata kuliah.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $classLeaders->links() }}</div>
@endsection
