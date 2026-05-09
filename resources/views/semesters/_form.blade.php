@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="name">Nama Semester</label>
        <input id="name" name="name" type="text" value="{{ old('name', $semester->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label>Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $semester->is_active ?? false))>
            Aktif
        </label>
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('semesters.index') }}">Batal</a>
</div>
