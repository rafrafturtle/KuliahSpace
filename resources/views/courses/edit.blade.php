@extends('layouts.app')

@section('title', 'Edit Mata Kuliah')
@section('subtitle', $course->code . ' - ' . $course->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('courses.update', $course) }}">
                @method('PUT')
                @include('courses._form', ['submitLabel' => 'Perbarui Mata Kuliah'])
            </form>
        </div>
    </div>
@endsection
