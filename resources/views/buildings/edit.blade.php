@extends('layouts.app')

@section('title', 'Edit Gedung')
@section('subtitle', $building->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('buildings.update', $building) }}">
                @method('PUT')
                @include('buildings._form', ['submitLabel' => 'Perbarui Gedung'])
            </form>
        </div>
    </div>
@endsection
