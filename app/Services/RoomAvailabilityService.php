<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\Room;
use App\Models\RoomRequest;
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
        $rooms = Room::query()
            ->where('is_active', true)
            ->when(! empty($criteria['capacity']), fn ($query) => $query->where('capacity', '>=', (int) $criteria['capacity']))
            ->orderBy('code')
            ->get();

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
}
