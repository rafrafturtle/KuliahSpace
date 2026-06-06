<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $buildings = Building::query()
            ->withCount('rooms')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('floor', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('buildings.index', compact('buildings', 'search'));
    }

    public function create(): View
    {
        return view('buildings.create', ['building' => new Building(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Building::create($this->validatedData($request));

        return redirect()->route('buildings.index')->with('success', 'Gedung berhasil ditambahkan.');
    }

    public function show(Building $building): View
    {
        $building->load(['rooms' => fn ($query) => $query->orderBy('code')]);

        return view('buildings.show', compact('building'));
    }

    public function edit(Building $building): View
    {
        return view('buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $building->update($this->validatedData($request, $building));

        $building->rooms()->update(['building' => $building->name]);

        return redirect()->route('buildings.index')->with('success', 'Gedung berhasil diperbarui.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        if ($building->rooms()->exists()) {
            return redirect()
                ->route('buildings.index')
                ->withErrors(['building' => 'Gedung masih memiliki ruangan. Pindahkan ruangan terlebih dahulu sebelum menghapus gedung.']);
        }

        $building->delete();

        return redirect()->route('buildings.index')->with('success', 'Gedung berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Building $building = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('buildings', 'code')->ignore($building?->id)],
            'floor' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
