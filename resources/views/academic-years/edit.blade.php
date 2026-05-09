@extends('layouts.app')

@section('title', 'Edit Tahun Akademik')
@section('subtitle', $academicYear->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('academic-years.update', $academicYear) }}">
                @method('PUT')
                @include('academic-years._form', ['submitLabel' => 'Perbarui Tahun Akademik'])
            </form>
        </div>
    </div>
@endsection
