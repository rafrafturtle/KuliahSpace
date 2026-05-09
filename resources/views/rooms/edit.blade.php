@extends('layouts.app')

@section('title', 'Edit Ruangan')
@section('subtitle', $room->code . ' - ' . $room->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('rooms.update', $room) }}">
                @method('PUT')
                @include('rooms._form', ['submitLabel' => 'Perbarui Ruangan'])
            </form>
        </div>
    </div>
@endsection
