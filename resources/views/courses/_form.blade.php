@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="code">Kode Mata Kuliah</label>
        <input id="code" name="code" type="text" value="{{ old('code', $course->code) }}" required>
        @error('code') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field">
        <label for="credits">SKS</label>
        <input id="credits" name="credits" type="number" min="1" max="10" value="{{ old('credits', $course->credits) }}">
        @error('credits') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="form-field full">
        <label for="name">Nama Mata Kuliah</label>
        <input id="name" name="name" type="text" value="{{ old('name', $course->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('courses.index') }}">Batal</a>
</div>
