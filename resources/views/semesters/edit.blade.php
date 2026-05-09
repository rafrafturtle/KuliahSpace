@extends('layouts.app')

@section('title', 'Edit Semester')
@section('subtitle', $semester->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('semesters.update', $semester) }}">
                @method('PUT')
                @include('semesters._form', ['submitLabel' => 'Perbarui Semester'])
            </form>
        </div>
    </div>
@endsection
