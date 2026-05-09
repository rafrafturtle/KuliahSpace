@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="code">Kode Ruangan</label>
        <input id="code" name="code" type="text" value="{{ old('code', $room->code) }}" required>
        @error('code') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label for="name">Nama Ruangan</label>
        <input id="name" name="name" type="text" value="{{ old('name', $room->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label for="building">Gedung</label>
        <input id="building" name="building" type="text" value="{{ old('building', $room->building) }}">
    </div>
    <div class="form-field">
        <label for="floor">Lantai</label>
        <input id="floor" name="floor" type="text" value="{{ old('floor', $room->floor) }}">
    </div>
    <div class="form-field">
        <label for="capacity">Kapasitas</label>
        <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $room->capacity) }}" required>
        @error('capacity') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label>Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $room->is_active ?? true))>
            Aktif
        </label>
    </div>
    <div class="form-field full">
        <label for="facilities">Fasilitas</label>
        <textarea id="facilities" name="facilities">{{ old('facilities', $room->facilities) }}</textarea>
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('rooms.index') }}">Batal</a>
</div>
