<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\Semester;
use App\Services\RoomAvailabilityService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(RoomAvailabilityService $availabilityService): View
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $today = now()->toDateString();
        $dayOfWeek = now()->format('l');

        $availableRoomsToday = 0;

        if ($activeSemester && $activeAcademicYear) {
            $availableRoomsToday = $availabilityService->getAvailableRooms([
                'day_of_week' => $dayOfWeek,
                'date' => $today,
                'start_time' => '00:00',
                'end_time' => '23:59',
                'semester_id' => $activeSemester->id,
                'academic_year_id' => $activeAcademicYear->id,
            ])->count();
        }

        return view('dashboard.index', [
            'totalActiveRooms' => Room::where('is_active', true)->count(),
            'totalActiveSchedules' => ClassSchedule::where('is_active', true)->count(),
            'pendingRoomRequests' => RoomRequest::where('status', RoomRequest::STATUS_PENDING)->count(),
            'approvedRoomRequests' => RoomRequest::where('status', RoomRequest::STATUS_APPROVED)->count(),
            'availableRoomsToday' => $availableRoomsToday,
            'recentRoomRequests' => RoomRequest::with(['requester', 'room'])
                ->latest()
                ->limit(6)
                ->get(),
            'activeSemester' => $activeSemester,
            'activeAcademicYear' => $activeAcademicYear,
        ]);
    }
}
