<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\Semester;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomSearchController extends Controller
{
    public function index(Request $request, RoomAvailabilityService $availabilityService): View
    {
        $searchKeys = ['date', 'start_time', 'end_time', 'semester_id', 'academic_year_id', 'capacity', 'building'];
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
        ]);

        $availability = $searchSubmitted && filled($criteria['date'] ?? null)
            ? $availabilityService->getAvailabilityByDate(
                $criteria['date'],
                $criteria['start_time'] ?? null,
                $criteria['end_time'] ?? null,
                $criteria['semester_id'] ?? null,
                $criteria['academic_year_id'] ?? null,
                $criteria['capacity'] ?? null,
                $criteria['building'] ?? null,
            )
            : null;

        return view('room-search.index', [
            'availability' => $availability,
            'criteria' => $criteria,
            'searchSubmitted' => $searchSubmitted,
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'buildings' => Room::where('is_active', true)
                ->whereNotNull('building')
                ->where('building', '<>', '')
                ->distinct()
                ->orderBy('building')
                ->pluck('building'),
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
