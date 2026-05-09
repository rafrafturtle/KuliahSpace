@extends('layouts.app')

@section('title', 'Edit Permintaan Ruangan')
@section('subtitle', $roomRequest->room?->code . ' · ' . $roomRequest->request_date?->format('d M Y'))

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('room-requests.update', $roomRequest) }}">
                @method('PUT')
                @include('room-requests._form', ['submitLabel' => 'Perbarui Permintaan', 'showStatus' => true])
            </form>
        </div>
    </div>
@endsection
