<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $semesters = Semester::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('semesters.index', compact('semesters', 'search'));
    }

    public function create(): View
    {
        return view('semesters.create', ['semester' => new Semester(['is_active' => false])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated): void {
            if ($validated['is_active']) {
                Semester::query()->update(['is_active' => false]);
            }

            Semester::create($validated);
        });

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil ditambahkan.');
    }

    public function edit(Semester $semester): View
    {
        return view('semesters.edit', compact('semester'));
    }

    public function update(Request $request, Semester $semester): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($semester, $validated): void {
            if ($validated['is_active']) {
                Semester::whereKeyNot($semester->id)->update(['is_active' => false]);
            }

            $semester->update($validated);
        });

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        $semester->delete();

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
