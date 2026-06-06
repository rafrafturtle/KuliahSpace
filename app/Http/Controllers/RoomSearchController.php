<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Building;
use App\Models\Semester;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomSearchController extends Controller
{
    public function index(Request $request, RoomAvailabilityService $availabilityService): View
    {
        $searchKeys = ['date', 'start_time', 'end_time', 'semester_id', 'academic_year_id', 'capacity', 'building', 'building_id'];
        $searchSubmitted = collect($searchKeys)->contains(fn (string $key) => $request->has($key));

        $this->ensureCompleteTimeRange($request);

        $criteria = $request->validate([
            'date' => [$searchSubmitted ? 'required' : 'nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'semester_id' => ['nullable', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'building' => ['nullable', 'string', 'max:255'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
        ]);

        if (empty($criteria['building_id']) && ! empty($criteria['building'])) {
            $legacyBuilding = Building::where('name', $criteria['building'])->first();
            $criteria['building_id'] = $legacyBuilding?->id;
        }

        $availability = $searchSubmitted && filled($criteria['date'] ?? null)
            ? $availabilityService->getAvailabilityByDate(
                $criteria['date'],
                $criteria['start_time'] ?? null,
                $criteria['end_time'] ?? null,
                $criteria['semester_id'] ?? null,
                $criteria['academic_year_id'] ?? null,
                $criteria['capacity'] ?? null,
                empty($criteria['building_id']) ? ($criteria['building'] ?? null) : null,
                $criteria['building_id'] ?? null,
            )
            : null;

        return view('room-search.index', [
            'availability' => $availability,
            'criteria' => $criteria,
            'searchSubmitted' => $searchSubmitted,
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'buildings' => Building::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function ensureCompleteTimeRange(Request $request): void
    {
        if ($request->filled('start_time') && ! $request->filled('end_time')) {
            throw ValidationException::withMessages([
                'end_time' => 'Jam selesai wajib diisi jika jam mulai diisi.',
            ]);
        }

        if (! $request->filled('start_time') && $request->filled('end_time')) {
            throw ValidationException::withMessages([
                'start_time' => 'Jam mulai wajib diisi jika jam selesai diisi.',
            ]);
        }
    }
}
