@extends('layouts.app')

@section('title', 'Ajukan Ruangan')
@section('subtitle', 'Request baru otomatis berstatus pending dan dicek bentrok.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('room-requests.store') }}">
                @include('room-requests._form', ['submitLabel' => 'Kirim Permintaan', 'showStatus' => false])
            </form>
        </div>
    </div>
@endsection
