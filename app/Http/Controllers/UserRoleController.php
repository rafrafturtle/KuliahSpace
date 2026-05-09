<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserRoleController extends Controller
{
    public function edit(User $user): View
    {
        $user->load('roles');

        return view('users.roles', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(array_keys($this->roles()))],
        ]);

        foreach ($this->roles() as $name => $title) {
            Bouncer::role()->firstOrCreate(['name' => $name], ['title' => $title]);
        }

        Bouncer::sync($user)->roles($validated['roles'] ?? []);
        Bouncer::refreshFor($user);

        return redirect()->route('users.show', $user)->with('success', 'Role pengguna berhasil diperbarui.');
    }

    private function roles(): array
    {
        return [
            'admin' => 'Admin',
            'dosen' => 'Dosen',
            'ketua_kelas' => 'Ketua Kelas Mata Kuliah',
            'mahasiswa' => 'Mahasiswa',
        ];
    }
}
