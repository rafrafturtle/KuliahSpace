@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="name">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="form-field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="form-field full">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" value="">
        <div class="muted">Opsional. Kosongkan saat edit jika password tidak ingin diubah.</div>
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('users.index') }}">Batal</a>
</div>
