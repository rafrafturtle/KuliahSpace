<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Building;
use App\Models\Semester;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomAvailabilityController extends Controller
{
    public function index(Request $request, RoomAvailabilityService $availabilityService): View
    {
        $criteria = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $calendarMonth = $this->calendarMonth($criteria, null);

        return view('room-availability.index', $this->formData([
            'criteria' => $criteria,
            'calendarMonth' => $calendarMonth,
            'calendarWeeks' => $this->calendarWeeks($calendarMonth, null),
            'calendarSummary' => $availabilityService->getCalendarSummary(
                (int) $calendarMonth->month,
                (int) $calendarMonth->year
            ),
            'previousMonth' => $calendarMonth->copy()->subMonth(),
            'nextMonth' => $calendarMonth->copy()->addMonth(),
        ]));
    }

    public function dateDetail(string $date, Request $request, RoomAvailabilityService $availabilityService): View
    {
        $selectedDate = $this->parseDateOrFail($date);

        $this->ensureCompleteTimeRange($request);

        $filters = $request->validate([
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'semester_id' => ['nullable', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'building' => ['nullable', 'string', 'max:255'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        if (empty($filters['building_id']) && ! empty($filters['building'])) {
            $legacyBuilding = Building::where('name', $filters['building'])->first();
            $filters['building_id'] = $legacyBuilding?->id;
        }

        $buildingAvailability = $availabilityService->getBuildingsAvailabilityByDate(
            $selectedDate->toDateString(),
            $filters['start_time'] ?? null,
            $filters['end_time'] ?? null,
            $filters['semester_id'] ?? null,
            $filters['academic_year_id'] ?? null,
            $filters['capacity'] ?? null,
        );

        $selectedBuilding = null;
        $selectedRoom = null;
        $roomsAvailability = null;
        $roomTimeline = null;

        if (! empty($filters['building_id'])) {
            $selectedBuilding = Building::findOrFail($filters['building_id']);
        }

        if (! empty($filters['room_id'])) {
            $roomTimeline = $availabilityService->getRoomTimeline(
                $selectedDate->toDateString(),
                $filters['room_id'],
                $filters['start_time'] ?? null,
                $filters['end_time'] ?? null,
                $filters['semester_id'] ?? null,
                $filters['academic_year_id'] ?? null,
                $filters['capacity'] ?? null,
            );

            abort_if($roomTimeline === null, 404);

            $selectedRoom = $roomTimeline['room'];

            if (! $selectedBuilding && $selectedRoom->building_id) {
                $selectedBuilding = $selectedRoom->buildingRecord;
                $filters['building_id'] = $selectedRoom->building_id;
            }

            if ($selectedBuilding && $selectedRoom->building_id !== $selectedBuilding->id) {
                abort(404);
            }
        }

        if ($selectedBuilding) {
            $roomsAvailability = $availabilityService->getRoomsAvailabilityByBuilding(
                $selectedDate->toDateString(),
                $selectedBuilding->id,
                $filters['start_time'] ?? null,
                $filters['end_time'] ?? null,
                $filters['semester_id'] ?? null,
                $filters['academic_year_id'] ?? null,
                $filters['capacity'] ?? null,
            );
        }

        $backQuery = [
            'month' => $filters['month'] ?? $selectedDate->month,
            'year' => $filters['year'] ?? $selectedDate->year,
        ];

        return view('room-availability.detail', $this->formData([
            'buildingAvailability' => $buildingAvailability,
            'roomsAvailability' => $roomsAvailability,
            'roomTimeline' => $roomTimeline,
            'criteria' => $filters,
            'selectedDate' => $selectedDate,
            'selectedDayLabel' => $this->dayLabels()[$selectedDate->format('l')],
            'selectedBuilding' => $selectedBuilding,
            'selectedRoom' => $selectedRoom,
            'backQuery' => $backQuery,
            'totalRooms' => $buildingAvailability['totalRooms'],
        ]));
    }

    public function check(Request $request): RedirectResponse
    {
        $this->ensureCompleteTimeRange($request);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'semester_id' => ['nullable', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'building' => ['nullable', 'string', 'max:255'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
        ]);

        return redirect()->route('room-search.index', array_filter(
            $validated,
            fn ($value) => filled($value)
        ));
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

    private function formData(array $extra = []): array
    {
        return $extra + [
            'availability' => null,
            'timeline' => null,
            'buildingAvailability' => null,
            'roomsAvailability' => null,
            'roomTimeline' => null,
            'criteria' => [],
            'calendarMonth' => now()->startOfMonth(),
            'calendarWeeks' => [],
            'calendarSummary' => collect(),
            'previousMonth' => now()->startOfMonth()->subMonth(),
            'nextMonth' => now()->startOfMonth()->addMonth(),
            'selectedDate' => null,
            'selectedDayLabel' => null,
            'selectedBuilding' => null,
            'selectedRoom' => null,
            'backQuery' => [],
            'totalRooms' => 0,
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'buildings' => Building::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function calendarMonth(array $criteria, ?string $selectedDate): Carbon
    {
        if ($selectedDate) {
            return Carbon::parse($selectedDate)->startOfMonth();
        }

        return Carbon::create(
            (int) ($criteria['year'] ?? now()->year),
            (int) ($criteria['month'] ?? now()->month),
            1
        )->startOfMonth();
    }

    private function calendarWeeks(Carbon $month, ?string $selectedDate): array
    {
        $cursor = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];

        while ($cursor->lte($endDate)) {
            $week = [];

            for ($day = 0; $day < 7; $day++) {
                $week[] = [
                    'date' => $cursor->copy(),
                    'inMonth' => $cursor->month === $month->month,
                    'isToday' => $cursor->isToday(),
                    'isSelected' => $selectedDate && $cursor->isSameDay(Carbon::parse($selectedDate)),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    private function parseDateOrFail(string $date): Carbon
    {
        try {
            $selectedDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (InvalidFormatException) {
            abort(404);
        }

        if ($selectedDate === false || $selectedDate->format('Y-m-d') !== $date) {
            abort(404);
        }

        return $selectedDate;
    }

    private function dayLabels(): array
    {
        return [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
    }
}
