<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Room;
use App\Models\Semester;
use App\Models\User;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $dayLabels = $this->dayLabels();
        $activeSemester = Semester::where('is_active', true)->first();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'day_of_week' => ['nullable', 'string', Rule::in(array_keys($dayLabels))],
            'start_time' => ['nullable', 'required_with:end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_with:start_time', 'date_format:H:i', 'after:start_time'],
            'semester_id' => ['nullable', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['nullable', 'uuid', 'exists:academic_years,id'],
        ]);

        $selectedSemesterId = $request->has('semester_id') ? ($filters['semester_id'] ?? null) : $activeSemester?->id;
        $selectedAcademicYearId = $request->has('academic_year_id') ? ($filters['academic_year_id'] ?? null) : $activeAcademicYear?->id;
        $hasTimeRange = ! empty($filters['start_time']) && ! empty($filters['end_time']);

        $schedules = ClassSchedule::with(['course', 'lecturer', 'room', 'semester', 'academicYear'])
            ->where('is_active', true)
            ->when($filters['day_of_week'] ?? null, fn ($query, $day) => $query->where('day_of_week', $day))
            ->when($selectedSemesterId, fn ($query) => $query->where('semester_id', $selectedSemesterId))
            ->when($selectedAcademicYearId, fn ($query) => $query->where('academic_year_id', $selectedAcademicYearId))
            ->when($hasTimeRange, function ($query) use ($filters): void {
                $query->where('start_time', '<', $filters['end_time'])
                    ->where('end_time', '>', $filters['start_time']);
            })
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('class_name', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('start_time')
            ->get();

        $activeRooms = Room::where('is_active', true)->orderBy('code')->get();
        $schedulesByDay = $schedules->groupBy('day_of_week');
        $displayDays = ($filters['day_of_week'] ?? null)
            ? [$filters['day_of_week'] => $dayLabels[$filters['day_of_week']]]
            : $dayLabels;

        $weeklySchedule = collect($displayDays)->map(function (string $label, string $day) use ($activeRooms, $schedulesByDay): array {
            $usedSchedules = $schedulesByDay->get($day, collect())->sortBy('start_time')->values();
            $usedRoomIds = $usedSchedules->pluck('room_id')->unique();
            $availableRooms = $activeRooms
                ->reject(fn (Room $room) => $usedRoomIds->contains($room->id))
                ->values();

            return [
                'day' => $day,
                'label' => $label,
                'usedSchedules' => $usedSchedules,
                'availableRooms' => $availableRooms,
            ];
        });

        return view('schedules.index', [
            'search' => $search,
            'weeklySchedule' => $weeklySchedule,
            'dayLabels' => $dayLabels,
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'filters' => $filters + [
                'semester_id' => $selectedSemesterId,
                'academic_year_id' => $selectedAcademicYearId,
            ],
            'hasTimeRange' => $hasTimeRange,
        ]);
    }

    public function create(): View
    {
        return view('schedules.create', $this->formData(new ClassSchedule(['is_active' => true])));
    }

    public function store(Request $request, RoomAvailabilityService $availabilityService): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $this->ensureRoomIsAvailable($availabilityService, $validated);

        ClassSchedule::create($validated);

        return redirect()->route('schedules.index')->with('success', 'Jadwal kuliah berhasil ditambahkan.');
    }

    public function show(ClassSchedule $schedule): View
    {
        $schedule->load(['course', 'lecturer', 'room', 'semester', 'academicYear']);

        return view('schedules.show', compact('schedule'));
    }

    public function edit(ClassSchedule $schedule): View
    {
        return view('schedules.edit', $this->formData($schedule));
    }

    public function update(Request $request, ClassSchedule $schedule, RoomAvailabilityService $availabilityService): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $this->ensureRoomIsAvailable($availabilityService, $validated, $schedule);

        $schedule->update($validated);

        return redirect()->route('schedules.index')->with('success', 'Jadwal kuliah berhasil diperbarui.');
    }

    public function destroy(ClassSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Jadwal kuliah berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'course_id' => ['required', 'uuid', 'exists:courses,id'],
            'lecturer_id' => ['required', 'uuid', 'exists:users,id'],
            'room_id' => ['required', 'uuid', 'exists:rooms,id'],
            'class_name' => ['required', 'string', 'max:100'],
            'day_of_week' => ['required', 'string', Rule::in($this->days())],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'week_number' => ['nullable', 'integer', 'min:1'],
            'semester_id' => ['required', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function ensureRoomIsAvailable(RoomAvailabilityService $availabilityService, array $validated, ?ClassSchedule $schedule = null): void
    {
        if (! $validated['is_active']) {
            return;
        }

        $errors = $availabilityService->validateAvailability([
            'room_id' => $validated['room_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'semester_id' => $validated['semester_id'],
            'academic_year_id' => $validated['academic_year_id'],
        ], true, $schedule?->id);

        if ($errors !== []) {
            throw ValidationException::withMessages(['room_id' => $errors[0]]);
        }
    }

    private function formData(ClassSchedule $schedule): array
    {
        return [
            'schedule' => $schedule,
            'courses' => Course::orderBy('code')->get(),
            'lecturers' => User::with('roles')->orderBy('name')->get(),
            'rooms' => Room::where('is_active', true)->orderBy('code')->get(),
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'days' => $this->days(),
        ];
    }

    private function days(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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
