@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="name">Nama Gedung</label>
        <input id="name" name="name" type="text" value="{{ old('name', $building->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label for="code">Kode Gedung</label>
        <input id="code" name="code" type="text" value="{{ old('code', $building->code) }}">
        @error('code') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label for="floor">Lantai</label>
        <input id="floor" name="floor" type="text" value="{{ old('floor', $building->floor) }}" placeholder="Contoh: 1, 2, 3">
        @error('floor') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label>Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $building->is_active ?? true))>
            Aktif
        </label>
    </div>
    <div class="form-field full">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description">{{ old('description', $building->description) }}</textarea>
        @error('description') <span class="field-error">{{ $message }}</span> @enderror
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('buildings.index') }}">Batal</a>
</div>
