<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomAvailabilityController extends Controller
{
    public function index(): View
    {
        return view('room-availability.index', $this->formData());
    }

    public function check(Request $request, RoomAvailabilityService $availabilityService): View
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'string', Rule::in($this->days())],
            'date' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'semester_id' => ['required', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'include_pending' => ['nullable', 'boolean'],
        ]);

        $includePending = $request->boolean('include_pending', true);
        $availableRooms = $availabilityService->getAvailableRooms($validated, $includePending);

        return view('room-availability.index', $this->formData([
            'availableRooms' => $availableRooms,
            'criteria' => $validated,
            'includePending' => $includePending,
        ]));
    }

    private function formData(array $extra = []): array
    {
        return $extra + [
            'availableRooms' => null,
            'criteria' => [],
            'includePending' => true,
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'days' => $this->days(),
        ];
    }

    private function days(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }
}
