<?php

namespace App\Services;

use App\Models\Building;
use App\Models\ClassSchedule;
use App\Models\Room;
use App\Models\RoomRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoomAvailabilityService
{
    public function hasScheduleConflict(
        string $roomId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        string $semesterId,
        string $academicYearId,
        ?string $excludeScheduleId = null
    ): bool {
        return ClassSchedule::query()
            ->where('room_id', $roomId)
            ->where('day_of_week', $dayOfWeek)
            ->where('semester_id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->when($excludeScheduleId, fn ($query) => $query->whereKeyNot($excludeScheduleId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function hasRequestConflict(
        string $roomId,
        string $requestDate,
        string $startTime,
        string $endTime,
        bool $includePending = true,
        ?string $excludeRequestId = null
    ): bool {
        $blockedStatuses = [RoomRequest::STATUS_APPROVED];

        if ($includePending) {
            $blockedStatuses[] = RoomRequest::STATUS_PENDING;
        }

        return RoomRequest::query()
            ->where('room_id', $roomId)
            ->whereDate('request_date', $requestDate)
            ->whereIn('status', $blockedStatuses)
            ->when($excludeRequestId, fn ($query) => $query->whereKeyNot($excludeRequestId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    /**
     * @param  array{
     *     room_id:string,
     *     day_of_week:string,
     *     start_time:string,
     *     end_time:string,
     *     semester_id?:string|null,
     *     academic_year_id?:string|null,
     *     request_date?:string|null
     * }  $criteria
     * @return array<int, string>
     */
    public function validateAvailability(array $criteria, bool $includePending = true, ?string $excludeScheduleId = null, ?string $excludeRequestId = null): array
    {
        $errors = [];

        if (! empty($criteria['semester_id']) && ! empty($criteria['academic_year_id'])) {
            $hasScheduleConflict = $this->hasScheduleConflict(
                $criteria['room_id'],
                $criteria['day_of_week'],
                $criteria['start_time'],
                $criteria['end_time'],
                $criteria['semester_id'],
                $criteria['academic_year_id'],
                $excludeScheduleId
            );

            if ($hasScheduleConflict) {
                $errors[] = 'Ruangan bentrok dengan jadwal kuliah aktif pada waktu tersebut.';
            }
        }

        if (! empty($criteria['request_date'])) {
            $hasRequestConflict = $this->hasRequestConflict(
                $criteria['room_id'],
                $criteria['request_date'],
                $criteria['start_time'],
                $criteria['end_time'],
                $includePending,
                $excludeRequestId
            );

            if ($hasRequestConflict) {
                $errors[] = 'Ruangan bentrok dengan permintaan penggunaan ruangan yang sudah disetujui atau masih pending.';
            }
        }

        return $errors;
    }

    /**
     * @param  array{
     *     day_of_week:string,
     *     start_time:string,
     *     end_time:string,
     *     semester_id:string,
     *     academic_year_id:string,
     *     date?:string|null,
     *     request_date?:string|null,
     *     capacity?:int|string|null
     * }  $criteria
     * @return Collection<int, Room>
     */
    public function getAvailableRooms(array $criteria, bool $includePending = true): Collection
    {
        $rooms = $this->activeRoomQuery(
            $criteria['capacity'] ?? null,
            $criteria['building'] ?? null,
            $criteria['building_id'] ?? null
        )->get();

        $requestDate = $criteria['request_date'] ?? $criteria['date'] ?? null;

        return $rooms->filter(function (Room $room) use ($criteria, $includePending, $requestDate): bool {
            return $this->validateAvailability([
                'room_id' => $room->id,
                'day_of_week' => $criteria['day_of_week'],
                'start_time' => $criteria['start_time'],
                'end_time' => $criteria['end_time'],
                'semester_id' => $criteria['semester_id'] ?? null,
                'academic_year_id' => $criteria['academic_year_id'] ?? null,
                'request_date' => $requestDate,
            ], $includePending) === [];
        })->values();
    }

    /**
     * @return array{
     *     used:Collection<int, array<string, mixed>>,
     *     pending:Collection<int, array<string, mixed>>,
     *     available:Collection<int, Room>,
     *     usedRoomCount:int,
     *     pendingRoomCount:int,
     *     availableRoomCount:int
     * }
     */
    public function getAvailabilityByDate(
        string $date,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $semesterId = null,
        ?string $academicYearId = null,
        int|string|null $capacity = null,
        ?string $building = null,
        ?string $buildingId = null
    ): array {
        $dayOfWeek = Carbon::parse($date)->format('l');
        $hasTimeRange = filled($startTime) && filled($endTime);
        $rooms = $this->activeRoomQuery($capacity, $building, $buildingId)->get()->keyBy('id');
        $roomIds = $rooms->keys()->all();

        if ($rooms->isEmpty()) {
            return [
                'used' => collect(),
                'pending' => collect(),
                'available' => collect(),
                'usedRoomCount' => 0,
                'pendingRoomCount' => 0,
                'availableRoomCount' => 0,
            ];
        }

        $schedules = ClassSchedule::with(['room', 'course', 'lecturer', 'semester', 'academicYear'])
            ->whereIn('room_id', $roomIds)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when($semesterId, fn (Builder $query) => $query->where('semester_id', $semesterId))
            ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->when($hasTimeRange, fn (Builder $query) => $this->overlappingTime($query, $startTime, $endTime))
            ->orderBy('start_time')
            ->get();

        $approvedRequests = RoomRequest::with(['room', 'requester'])
            ->whereIn('room_id', $roomIds)
            ->whereDate('request_date', $date)
            ->where('status', RoomRequest::STATUS_APPROVED)
            ->when($hasTimeRange, fn (Builder $query) => $this->overlappingTime($query, $startTime, $endTime))
            ->orderBy('start_time')
            ->get();

        $usedRoomIds = $schedules
            ->pluck('room_id')
            ->merge($approvedRequests->pluck('room_id'))
            ->unique()
            ->values();

        $pendingRequests = RoomRequest::with(['room', 'requester'])
            ->whereIn('room_id', $roomIds)
            ->whereDate('request_date', $date)
            ->where('status', RoomRequest::STATUS_PENDING)
            ->when($hasTimeRange, fn (Builder $query) => $this->overlappingTime($query, $startTime, $endTime))
            ->orderBy('start_time')
            ->get()
            ->reject(fn (RoomRequest $request) => $usedRoomIds->contains($request->room_id))
            ->values();

        $pendingRoomIds = $pendingRequests->pluck('room_id')->unique()->values();

        $used = $schedules
            ->map(fn (ClassSchedule $schedule): array => [
                'source' => 'Jadwal Tetap',
                'room' => $schedule->room,
                'course' => $schedule->course,
                'lecturer' => $schedule->lecturer,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'purpose' => null,
                'requester' => null,
            ])
            ->toBase()
            ->merge($approvedRequests->map(fn (RoomRequest $request): array => [
                'source' => 'Pengajuan Disetujui',
                'room' => $request->room,
                'course' => null,
                'lecturer' => null,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'purpose' => $request->purpose,
                'requester' => $request->requester,
            ]))
            ->sortBy([
                fn (array $item) => $item['room']?->code,
                fn (array $item) => $item['start_time'],
            ])
            ->values();

        $pending = $pendingRequests->map(fn (RoomRequest $request): array => [
            'room' => $request->room,
            'requester' => $request->requester,
            'request_date' => $request->request_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose' => $request->purpose,
        ]);

        $available = $rooms
            ->reject(fn (Room $room) => $usedRoomIds->contains($room->id) || $pendingRoomIds->contains($room->id))
            ->sortBy('code')
            ->values();

        return [
            'used' => $used,
            'pending' => $pending,
            'available' => $available,
            'usedRoomCount' => $usedRoomIds->count(),
            'pendingRoomCount' => $pendingRoomIds->count(),
            'availableRoomCount' => $available->count(),
        ];
    }

    /**
     * @return array{
     *     rooms:Collection<int, array<string, mixed>>,
     *     totalRooms:int,
     *     usedRoomCount:int,
     *     pendingRoomCount:int,
     *     availableRoomCount:int,
     *     fullyAvailableRoomCount:int,
     *     rangeStartTime:string,
     *     rangeEndTime:string,
     *     hasTimeFilter:bool
     * }
     */
    public function getRoomTimelineByDate(
        string $date,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $semesterId = null,
        ?string $academicYearId = null,
        int|string|null $capacity = null,
        ?string $building = null,
        ?string $buildingId = null
    ): array {
        $dayOfWeek = Carbon::parse($date)->format('l');
        $hasTimeFilter = filled($startTime) && filled($endTime);
        $rangeStartTime = $hasTimeFilter ? $this->normalizeTime($startTime) : '07:00';
        $rangeEndTime = $hasTimeFilter ? $this->normalizeTime($endTime) : '18:00';
        $rangeStart = $this->timeToMinutes($rangeStartTime);
        $rangeEnd = $this->timeToMinutes($rangeEndTime);

        $rooms = $this->activeRoomQuery($capacity, $building, $buildingId)->get();
        $roomIds = $rooms->pluck('id')->all();

        if ($rooms->isEmpty() || $rangeStart >= $rangeEnd) {
            return [
                'rooms' => collect(),
                'totalRooms' => 0,
                'usedRoomCount' => 0,
                'pendingRoomCount' => 0,
                'availableRoomCount' => 0,
                'fullyAvailableRoomCount' => 0,
                'rangeStartTime' => $rangeStartTime,
                'rangeEndTime' => $rangeEndTime,
                'hasTimeFilter' => $hasTimeFilter,
            ];
        }

        $schedules = ClassSchedule::with(['room', 'course', 'lecturer', 'semester', 'academicYear'])
            ->whereIn('room_id', $roomIds)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when($semesterId, fn (Builder $query) => $query->where('semester_id', $semesterId))
            ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->where('start_time', '<', $rangeEndTime)
            ->where('end_time', '>', $rangeStartTime)
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        $approvedRequests = RoomRequest::with(['room', 'requester'])
            ->whereIn('room_id', $roomIds)
            ->whereDate('request_date', $date)
            ->where('status', RoomRequest::STATUS_APPROVED)
            ->where('start_time', '<', $rangeEndTime)
            ->where('end_time', '>', $rangeStartTime)
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        $pendingRequests = RoomRequest::with(['room', 'requester'])
            ->whereIn('room_id', $roomIds)
            ->whereDate('request_date', $date)
            ->where('status', RoomRequest::STATUS_PENDING)
            ->where('start_time', '<', $rangeEndTime)
            ->where('end_time', '>', $rangeStartTime)
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        $roomTimelines = $rooms
            ->map(fn (Room $room): array => $this->buildRoomTimeline(
                $room,
                $schedules->get($room->id, collect()),
                $approvedRequests->get($room->id, collect()),
                $pendingRequests->get($room->id, collect()),
                $rangeStart,
                $rangeEnd,
                $hasTimeFilter
            ))
            ->values();

        return [
            'rooms' => $roomTimelines,
            'totalRooms' => $roomTimelines->count(),
            'usedRoomCount' => $roomTimelines->filter(fn (array $timeline): bool => count($timeline['used_slots']) > 0)->count(),
            'pendingRoomCount' => $roomTimelines->filter(fn (array $timeline): bool => count($timeline['pending_slots']) > 0)->count(),
            'availableRoomCount' => $roomTimelines->filter(fn (array $timeline): bool => count($timeline['available_slots']) > 0)->count(),
            'fullyAvailableRoomCount' => $roomTimelines->filter(fn (array $timeline): bool => $timeline['summary_status'] === 'fully_available')->count(),
            'rangeStartTime' => $rangeStartTime,
            'rangeEndTime' => $rangeEndTime,
            'hasTimeFilter' => $hasTimeFilter,
        ];
    }

    /**
     * @return array{
     *     buildings:Collection<int, array<string, mixed>>,
     *     totalBuildings:int,
     *     totalRooms:int,
     *     usedRoomCount:int,
     *     pendingRoomCount:int,
     *     availableRoomCount:int,
     *     fullyAvailableRoomCount:int,
     *     rangeStartTime:string,
     *     rangeEndTime:string,
     *     hasTimeFilter:bool
     * }
     */
    public function getBuildingsAvailabilityByDate(
        string $date,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $semesterId = null,
        ?string $academicYearId = null,
        int|string|null $capacity = null
    ): array {
        $timeline = $this->getRoomTimelineByDate(
            $date,
            $startTime,
            $endTime,
            $semesterId,
            $academicYearId,
            $capacity
        );

        $buildings = $timeline['rooms']
            ->filter(fn (array $timelineItem): bool => $timelineItem['room']->buildingRecord !== null)
            ->groupBy(fn (array $timelineItem): string => $timelineItem['room']->building_id)
            ->map(function (Collection $buildingRooms): array {
                $building = $buildingRooms->first()['room']->buildingRecord;

                return [
                    'building' => $building,
                    'total_rooms' => $buildingRooms->count(),
                    'used_rooms_count' => $buildingRooms->filter(fn (array $timelineItem): bool => count($timelineItem['used_slots']) > 0)->count(),
                    'pending_rooms_count' => $buildingRooms->filter(fn (array $timelineItem): bool => count($timelineItem['pending_slots']) > 0)->count(),
                    'available_rooms_count' => $buildingRooms->filter(fn (array $timelineItem): bool => count($timelineItem['available_slots']) > 0)->count(),
                    'fully_available_rooms_count' => $buildingRooms->filter(fn (array $timelineItem): bool => $timelineItem['summary_status'] === 'fully_available')->count(),
                ];
            })
            ->sortBy(fn (array $buildingSummary): string => $buildingSummary['building']->name)
            ->values();

        return [
            'buildings' => $buildings,
            'totalBuildings' => $buildings->count(),
            'totalRooms' => $timeline['totalRooms'],
            'usedRoomCount' => $timeline['usedRoomCount'],
            'pendingRoomCount' => $timeline['pendingRoomCount'],
            'availableRoomCount' => $timeline['availableRoomCount'],
            'fullyAvailableRoomCount' => $timeline['fullyAvailableRoomCount'],
            'rangeStartTime' => $timeline['rangeStartTime'],
            'rangeEndTime' => $timeline['rangeEndTime'],
            'hasTimeFilter' => $timeline['hasTimeFilter'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getRoomsAvailabilityByBuilding(
        string $date,
        string $buildingId,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $semesterId = null,
        ?string $academicYearId = null,
        int|string|null $capacity = null
    ): array {
        $timeline = $this->getRoomTimelineByDate(
            $date,
            $startTime,
            $endTime,
            $semesterId,
            $academicYearId,
            $capacity,
            null,
            $buildingId
        );

        return $timeline + [
            'building' => Building::find($buildingId),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRoomTimeline(
        string $date,
        string $roomId,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $semesterId = null,
        ?string $academicYearId = null,
        int|string|null $capacity = null
    ): ?array {
        $room = $this->activeRoomQuery($capacity)
            ->whereKey($roomId)
            ->first();

        if (! $room) {
            return null;
        }

        $timeline = $this->getRoomTimelineByDate(
            $date,
            $startTime,
            $endTime,
            $semesterId,
            $academicYearId,
            $capacity,
            null,
            $room->building_id
        );
        $timelineItem = $timeline['rooms']->first(fn (array $item): bool => $item['room']->id === $room->id);

        if (! $timelineItem) {
            return null;
        }

        return [
            'room' => $timelineItem['room'],
            'timeline' => $timelineItem,
            'rangeStartTime' => $timeline['rangeStartTime'],
            'rangeEndTime' => $timeline['rangeEndTime'],
            'hasTimeFilter' => $timeline['hasTimeFilter'],
        ];
    }

    public function getCalendarSummary(int $month, int $year, array $filters = []): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->mapWithKeys(function ($date) use ($filters): array {
                $availability = $this->getAvailabilityByDate(
                    $date->toDateString(),
                    $filters['start_time'] ?? null,
                    $filters['end_time'] ?? null,
                    $filters['semester_id'] ?? null,
                    $filters['academic_year_id'] ?? null,
                    $filters['capacity'] ?? null,
                    $filters['building'] ?? null,
                    $filters['building_id'] ?? null,
                );

                return [
                    $date->toDateString() => [
                        'used' => $availability['usedRoomCount'],
                        'available' => $availability['availableRoomCount'],
                        'pending' => $availability['pendingRoomCount'],
                    ],
                ];
            });
    }

    private function activeRoomQuery(int|string|null $capacity = null, ?string $building = null, ?string $buildingId = null): Builder
    {
        return Room::query()
            ->with('buildingRecord')
            ->where('is_active', true)
            ->when(filled($capacity), fn (Builder $query) => $query->where('capacity', '>=', (int) $capacity))
            ->when(filled($buildingId), fn (Builder $query) => $query->where('building_id', $buildingId))
            ->when(filled($building), fn (Builder $query) => $query->where('building', $building))
            ->orderBy('code');
    }

    private function overlappingTime(Builder $query, ?string $startTime, ?string $endTime): Builder
    {
        return $query
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /**
     * @param  Collection<int, ClassSchedule>  $schedules
     * @param  Collection<int, RoomRequest>  $approvedRequests
     * @param  Collection<int, RoomRequest>  $pendingRequests
     * @return array<string, mixed>
     */
    private function buildRoomTimeline(
        Room $room,
        Collection $schedules,
        Collection $approvedRequests,
        Collection $pendingRequests,
        int $rangeStart,
        int $rangeEnd,
        bool $hasTimeFilter
    ): array {
        $usedSlots = collect();

        foreach ($schedules as $schedule) {
            $slot = $this->clipSlot($schedule->start_time, $schedule->end_time, $rangeStart, $rangeEnd, [
                'label' => $schedule->course?->name ?? 'Jadwal Kuliah',
                'source' => 'Jadwal Tetap',
                'meta' => collect([$schedule->lecturer?->name, $schedule->class_name])->filter()->implode(' | '),
            ]);

            if ($slot) {
                $usedSlots->push($slot);
            }
        }

        foreach ($approvedRequests as $request) {
            $slot = $this->clipSlot($request->start_time, $request->end_time, $rangeStart, $rangeEnd, [
                'label' => $request->purpose,
                'source' => 'Pengajuan Disetujui',
                'meta' => $request->requester?->name,
            ]);

            if ($slot) {
                $usedSlots->push($slot);
            }
        }

        $usedSlots = $usedSlots
            ->sortBy([['start_minutes', 'asc'], ['end_minutes', 'asc']])
            ->values()
            ->all();

        $usedIntervals = $this->mergeIntervals($usedSlots);
        $pendingSlots = collect();

        foreach ($pendingRequests as $request) {
            $slot = $this->clipSlot($request->start_time, $request->end_time, $rangeStart, $rangeEnd, [
                'label' => $request->purpose,
                'source' => 'Pengajuan Pending',
                'meta' => $request->requester?->name,
            ]);

            if (! $slot) {
                continue;
            }

            foreach ($this->subtractDetailedInterval($slot, $usedIntervals) as $pendingSlot) {
                $pendingSlots->push($pendingSlot);
            }
        }

        $pendingSlots = $pendingSlots
            ->sortBy([['start_minutes', 'asc'], ['end_minutes', 'asc']])
            ->values()
            ->all();

        $availableSlots = $this->generateAvailableSlotsInMinutes(
            $usedIntervals,
            $this->mergeIntervals($pendingSlots),
            $rangeStart,
            $rangeEnd
        );
        $timelineRows = $this->buildTimelineRows($usedSlots, $pendingSlots, $rangeStart, $rangeEnd);

        return [
            'room' => $room,
            'used_slots' => $this->formatSlots($usedSlots),
            'pending_slots' => $this->formatSlots($pendingSlots),
            'available_slots' => $this->formatSlots($availableSlots),
            'timeline_rows' => $this->formatSlots($timelineRows),
            'summary_status' => $this->summaryStatus($usedSlots, $pendingSlots, $availableSlots),
            'has_time_filter' => $hasTimeFilter,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $usedSlots
     * @param  array<int, array<string, mixed>>  $pendingSlots
     * @return array<int, array<string, mixed>>
     */
    private function buildTimelineRows(array $usedSlots, array $pendingSlots, int $rangeStart, int $rangeEnd): array
    {
        $boundaries = collect([$rangeStart, $rangeEnd])
            ->merge(collect($usedSlots)->flatMap(fn (array $slot): array => [$slot['start_minutes'], $slot['end_minutes']]))
            ->merge(collect($pendingSlots)->flatMap(fn (array $slot): array => [$slot['start_minutes'], $slot['end_minutes']]))
            ->filter(fn (mixed $boundary): bool => is_int($boundary) && $boundary >= $rangeStart && $boundary <= $rangeEnd)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $rows = [];

        for ($index = 0; $index < count($boundaries) - 1; $index++) {
            $start = $boundaries[$index];
            $end = $boundaries[$index + 1];

            if ($start >= $end) {
                continue;
            }

            $usedSlot = $this->firstOverlappingSlot($usedSlots, $start, $end);
            $pendingSlot = $this->firstOverlappingSlot($pendingSlots, $start, $end);

            if ($usedSlot) {
                $rows[] = $this->timelineRowFromSlot($usedSlot, $start, $end, 'Terpakai');

                continue;
            }

            if ($pendingSlot) {
                $rows[] = $this->timelineRowFromSlot($pendingSlot, $start, $end, 'Pending');

                continue;
            }

            $rows[] = [
                'start_time' => $this->minutesToTime($start),
                'end_time' => $this->minutesToTime($end),
                'start_minutes' => $start,
                'end_minutes' => $end,
                'label' => 'Tersedia',
                'source' => null,
                'meta' => null,
                'status' => 'Tersedia',
            ];
        }

        return $this->mergeTimelineRows($rows);
    }

    /**
     * @param  array<int, array<string, mixed>>  $slots
     * @return array<string, mixed>|null
     */
    private function firstOverlappingSlot(array $slots, int $start, int $end): ?array
    {
        foreach ($slots as $slot) {
            if ($this->hasOverlapInMinutes($start, $end, $slot['start_minutes'], $slot['end_minutes'])) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $slot
     * @return array<string, mixed>
     */
    private function timelineRowFromSlot(array $slot, int $start, int $end, string $status): array
    {
        return [
            'start_time' => $this->minutesToTime($start),
            'end_time' => $this->minutesToTime($end),
            'start_minutes' => $start,
            'end_minutes' => $end,
            'label' => $slot['label'] ?? $status,
            'source' => $slot['source'] ?? null,
            'meta' => $slot['meta'] ?? null,
            'status' => $status,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function mergeTimelineRows(array $rows): array
    {
        $merged = [];

        foreach ($rows as $row) {
            $lastIndex = count($merged) - 1;

            if (
                $lastIndex >= 0
                && $merged[$lastIndex]['end_minutes'] === $row['start_minutes']
                && $merged[$lastIndex]['status'] === $row['status']
                && ($merged[$lastIndex]['label'] ?? null) === ($row['label'] ?? null)
                && ($merged[$lastIndex]['source'] ?? null) === ($row['source'] ?? null)
                && ($merged[$lastIndex]['meta'] ?? null) === ($row['meta'] ?? null)
            ) {
                $merged[$lastIndex]['end_minutes'] = $row['end_minutes'];
                $merged[$lastIndex]['end_time'] = $row['end_time'];

                continue;
            }

            $merged[] = $row;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>|null
     */
    private function clipSlot(mixed $startTime, mixed $endTime, int $rangeStart, int $rangeEnd, array $meta = []): ?array
    {
        $start = max($this->timeToMinutes((string) $startTime), $rangeStart);
        $end = min($this->timeToMinutes((string) $endTime), $rangeEnd);

        if ($start >= $end) {
            return null;
        }

        return $meta + [
            'start_time' => $this->minutesToTime($start),
            'end_time' => $this->minutesToTime($end),
            'start_minutes' => $start,
            'end_minutes' => $end,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $intervals
     * @return array<int, array<string, mixed>>
     */
    private function mergeIntervals(array $intervals): array
    {
        $normalized = collect($intervals)
            ->map(function (array $interval): ?array {
                $start = $interval['start_minutes'] ?? null;
                $end = $interval['end_minutes'] ?? null;

                if (! is_int($start) || ! is_int($end) || $start >= $end) {
                    return null;
                }

                return [
                    'start_minutes' => $start,
                    'end_minutes' => $end,
                ];
            })
            ->filter()
            ->sortBy([['start_minutes', 'asc'], ['end_minutes', 'asc']])
            ->values();

        $merged = [];

        foreach ($normalized as $interval) {
            $lastIndex = count($merged) - 1;

            if ($lastIndex < 0 || $interval['start_minutes'] > $merged[$lastIndex]['end_minutes']) {
                $merged[] = $interval;

                continue;
            }

            $merged[$lastIndex]['end_minutes'] = max($merged[$lastIndex]['end_minutes'], $interval['end_minutes']);
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $slot
     * @param  array<int, array<string, int>>  $blockedIntervals
     * @return array<int, array<string, mixed>>
     */
    private function subtractDetailedInterval(array $slot, array $blockedIntervals): array
    {
        $segments = [[
            'start_minutes' => $slot['start_minutes'],
            'end_minutes' => $slot['end_minutes'],
        ]];

        foreach ($blockedIntervals as $blocked) {
            $segments = $this->subtractIntervals($segments, [$blocked]);
        }

        return collect($segments)
            ->map(fn (array $segment): array => array_merge($slot, [
                'start_time' => $this->minutesToTime($segment['start_minutes']),
                'end_time' => $this->minutesToTime($segment['end_minutes']),
                'start_minutes' => $segment['start_minutes'],
                'end_minutes' => $segment['end_minutes'],
            ]))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, int>>  $usedIntervals
     * @param  array<int, array<string, int>>  $pendingIntervals
     * @return array<int, array<string, mixed>>
     */
    private function generateAvailableSlotsInMinutes(array $usedIntervals, array $pendingIntervals, int $dayStart, int $dayEnd): array
    {
        $blockedIntervals = $this->mergeIntervals(array_merge($usedIntervals, $pendingIntervals));
        $availableIntervals = $this->subtractIntervals([[
            'start_minutes' => $dayStart,
            'end_minutes' => $dayEnd,
        ]], $blockedIntervals);

        return collect($availableIntervals)
            ->map(fn (array $interval): array => [
                'start_time' => $this->minutesToTime($interval['start_minutes']),
                'end_time' => $this->minutesToTime($interval['end_minutes']),
                'start_minutes' => $interval['start_minutes'],
                'end_minutes' => $interval['end_minutes'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, int>>  $baseIntervals
     * @param  array<int, array<string, int>>  $blockedIntervals
     * @return array<int, array<string, int>>
     */
    private function subtractIntervals(array $baseIntervals, array $blockedIntervals): array
    {
        $result = [];

        foreach ($baseIntervals as $baseInterval) {
            $segments = [$baseInterval];

            foreach ($blockedIntervals as $blockedInterval) {
                $nextSegments = [];

                foreach ($segments as $segment) {
                    if (! $this->hasOverlapInMinutes(
                        $segment['start_minutes'],
                        $segment['end_minutes'],
                        $blockedInterval['start_minutes'],
                        $blockedInterval['end_minutes']
                    )) {
                        $nextSegments[] = $segment;

                        continue;
                    }

                    if ($blockedInterval['start_minutes'] > $segment['start_minutes']) {
                        $nextSegments[] = [
                            'start_minutes' => $segment['start_minutes'],
                            'end_minutes' => min($blockedInterval['start_minutes'], $segment['end_minutes']),
                        ];
                    }

                    if ($blockedInterval['end_minutes'] < $segment['end_minutes']) {
                        $nextSegments[] = [
                            'start_minutes' => max($blockedInterval['end_minutes'], $segment['start_minutes']),
                            'end_minutes' => $segment['end_minutes'],
                        ];
                    }
                }

                $segments = array_values(array_filter(
                    $nextSegments,
                    fn (array $segment): bool => $segment['start_minutes'] < $segment['end_minutes']
                ));
            }

            array_push($result, ...$segments);
        }

        return collect($result)
            ->sortBy([['start_minutes', 'asc'], ['end_minutes', 'asc']])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $slots
     * @return array<int, array<string, mixed>>
     */
    private function formatSlots(array $slots): array
    {
        return collect($slots)
            ->map(function (array $slot): array {
                unset($slot['start_minutes'], $slot['end_minutes']);

                return $slot;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $usedSlots
     * @param  array<int, array<string, mixed>>  $pendingSlots
     * @param  array<int, array<string, mixed>>  $availableSlots
     */
    private function summaryStatus(array $usedSlots, array $pendingSlots, array $availableSlots): string
    {
        if ($usedSlots === [] && $pendingSlots === []) {
            return 'fully_available';
        }

        if ($availableSlots === [] && $usedSlots !== []) {
            return 'fully_used';
        }

        if ($pendingSlots !== []) {
            return 'has_pending';
        }

        return 'partially_available';
    }

    public function hasOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return $this->hasOverlapInMinutes(
            $this->timeToMinutes($startA),
            $this->timeToMinutes($endA),
            $this->timeToMinutes($startB),
            $this->timeToMinutes($endB)
        );
    }

    private function hasOverlapInMinutes(int $startA, int $endA, int $startB, int $endB): bool
    {
        return $startA < $endB && $endA > $startB;
    }

    private function normalizeTime(?string $time): string
    {
        return substr((string) $time, 0, 5);
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_pad(explode(':', $this->normalizeTime($time)), 2, 0);

        return ((int) $hour * 60) + (int) $minute;
    }

    private function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
