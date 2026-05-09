<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $academicYears = AcademicYear::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_active')
            ->orderByDesc('name')
            ->paginate(10)
            ->withQueryString();

        return view('academic-years.index', compact('academicYears', 'search'));
    }

    public function create(): View
    {
        return view('academic-years.create', ['academicYear' => new AcademicYear(['is_active' => false])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated): void {
            if ($validated['is_active']) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            AcademicYear::create($validated);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($academicYear, $validated): void {
            if ($validated['is_active']) {
                AcademicYear::whereKeyNot($academicYear->id)->update(['is_active' => false]);
            }

            $academicYear->update($validated);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun akademik berhasil dihapus.');
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
