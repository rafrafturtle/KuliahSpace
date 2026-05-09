@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="requester_id">Pemohon</label>
        @if ($canChooseRequester)
            <select id="requester_id" name="requester_id" required>
                <option value="">Pilih pemohon</option>
                @foreach ($requesters as $requester)
                    <option value="{{ $requester->id }}" @selected(old('requester_id', $roomRequest->requester_id) === $requester->id)>
                        {{ $requester->name }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="requester_id" value="{{ auth()->id() }}">
            <input id="requester_id" type="text" value="{{ auth()->user()->name }}" disabled>
        @endif
    </div>
    <div class="form-field">
        <label for="room_id">Ruangan</label>
        <select id="room_id" name="room_id" required>
            <option value="">Pilih ruangan</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected(old('room_id', $roomRequest->room_id) === $room->id)>
                    {{ $room->code }} - {{ $room->name }} ({{ $room->capacity }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="request_date">Tanggal</label>
        <input id="request_date" name="request_date" type="date" value="{{ old('request_date', $roomRequest->request_date?->format('Y-m-d')) }}" required>
    </div>
    <div class="form-field">
        <label for="status">Status</label>
        @if (($showStatus ?? false) === true && $canEditStatus)
            <select id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $roomRequest->status) === $status)>{{ str($status)->headline() }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="status" value="{{ $roomRequest->status ?? 'pending' }}">
            @include('partials.status-badge', ['status' => $roomRequest->status ?? 'pending'])
        @endif
    </div>
    <div class="form-field">
        <label for="start_time">Mulai</label>
        <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $roomRequest->start_time ? substr($roomRequest->start_time, 0, 5) : '') }}" required>
    </div>
    <div class="form-field">
        <label for="end_time">Selesai</label>
        <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $roomRequest->end_time ? substr($roomRequest->end_time, 0, 5) : '') }}" required>
    </div>
    <div class="form-field full">
        <label for="purpose">Tujuan Penggunaan</label>
        <textarea id="purpose" name="purpose" required>{{ old('purpose', $roomRequest->purpose) }}</textarea>
    </div>
    <div class="form-field full">
        <label for="admin_note">Catatan Admin</label>
        <textarea id="admin_note" name="admin_note">{{ old('admin_note', $roomRequest->admin_note) }}</textarea>
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('room-requests.index') }}">Batal</a>
</div>
