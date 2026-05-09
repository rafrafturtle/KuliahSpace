<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\Semester;
use App\Models\User;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $roomRequests = RoomRequest::with(['requester', 'room', 'approver'])
            ->when(! $this->currentUserIsAdmin(), fn ($query) => $query->where('requester_id', $request->user()->id))
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('purpose', 'like', "%{$search}%")
                        ->orWhereHas('requester', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, RoomRequest::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->latest('request_date')
            ->paginate(10)
            ->withQueryString();

        return view('room-requests.index', [
            'roomRequests' => $roomRequests,
            'search' => $search,
            'status' => $status,
            'canCreateRoomRequest' => $this->canCreateRoomRequest(),
        ]);
    }

    public function create(): View
    {
        return view('room-requests.create', $this->formData(new RoomRequest(['status' => RoomRequest::STATUS_PENDING])));
    }

    public function store(Request $request, RoomAvailabilityService $availabilityService): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['requester_id'] = $this->currentUserIsAdmin()
            ? $validated['requester_id']
            : $request->user()->id;
        $validated['status'] = RoomRequest::STATUS_PENDING;
        $validated['day_of_week'] = Carbon::parse($validated['request_date'])->format('l');

        $this->ensureRequestIsAvailable($availabilityService, $validated);

        RoomRequest::create($validated);

        return redirect()->route('room-requests.index')->with('success', 'Permintaan ruangan berhasil dikirim.');
    }

    public function show(RoomRequest $roomRequest): View
    {
        $this->authorizeRoomRequestAccess($roomRequest);

        $roomRequest->load(['requester', 'room', 'approver']);

        return view('room-requests.show', [
            'roomRequest' => $roomRequest,
            'canApproveRequests' => $this->currentUserIsAdmin(),
        ]);
    }

    public function edit(RoomRequest $roomRequest): View
    {
        $this->authorizeRoomRequestAccess($roomRequest);

        return view('room-requests.edit', $this->formData($roomRequest));
    }

    public function update(Request $request, RoomRequest $roomRequest, RoomAvailabilityService $availabilityService): RedirectResponse
    {
        $this->authorizeRoomRequestAccess($roomRequest);

        $validated = $this->validatedData($request, $this->currentUserIsAdmin());
        $validated['requester_id'] = $this->currentUserIsAdmin()
            ? $validated['requester_id']
            : $request->user()->id;
        $validated['status'] = $this->currentUserIsAdmin()
            ? $validated['status']
            : $roomRequest->status;
        $validated['day_of_week'] = Carbon::parse($validated['request_date'])->format('l');

        if ($validated['status'] === RoomRequest::STATUS_APPROVED && $roomRequest->status !== RoomRequest::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => 'Gunakan aksi Setujui agar approved_by dan approved_at tersimpan dengan benar.',
            ]);
        }

        $this->ensureRequestIsAvailable($availabilityService, $validated, $roomRequest);

        if ($validated['status'] !== RoomRequest::STATUS_APPROVED) {
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
        }

        $roomRequest->update($validated);

        return redirect()->route('room-requests.index')->with('success', 'Permintaan ruangan berhasil diperbarui.');
    }

    public function approve(Request $request, RoomRequest $roomRequest, RoomAvailabilityService $availabilityService): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
        ]);

        $this->ensureRequestIsAvailable($availabilityService, [
            'room_id' => $roomRequest->room_id,
            'request_date' => $roomRequest->request_date->toDateString(),
            'day_of_week' => $roomRequest->day_of_week,
            'start_time' => $roomRequest->start_time,
            'end_time' => $roomRequest->end_time,
        ], $roomRequest, false);

        DB::transaction(function () use ($roomRequest, $validated): void {
            $roomRequest->update([
                'status' => RoomRequest::STATUS_APPROVED,
                'admin_note' => $validated['admin_note'] ?? $roomRequest->admin_note,
                'approved_by' => request()->user()->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('room-requests.show', $roomRequest)->with('success', 'Permintaan ruangan disetujui.');
    }

    public function reject(Request $request, RoomRequest $roomRequest): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
        ]);

        $roomRequest->update([
            'status' => RoomRequest::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'] ?? null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('room-requests.show', $roomRequest)->with('success', 'Permintaan ruangan ditolak.');
    }

    public function cancel(RoomRequest $roomRequest): RedirectResponse
    {
        $this->authorizeRoomRequestAccess($roomRequest);

        $roomRequest->update([
            'status' => RoomRequest::STATUS_CANCELLED,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('room-requests.show', $roomRequest)->with('success', 'Permintaan ruangan dibatalkan.');
    }

    private function validatedData(Request $request, bool $includeStatus = false): array
    {
        $rules = [
            'requester_id' => [$this->currentUserIsAdmin() ? 'required' : 'nullable', 'uuid', 'exists:users,id'],
            'room_id' => ['required', 'uuid', 'exists:rooms,id'],
            'request_date' => ['required', 'date'],
            'day_of_week' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string'],
            'admin_note' => ['nullable', 'string'],
        ];

        if ($includeStatus) {
            $rules['status'] = ['required', Rule::in(RoomRequest::STATUSES)];
        }

        return $request->validate($rules);
    }

    private function ensureRequestIsAvailable(RoomAvailabilityService $availabilityService, array $validated, ?RoomRequest $roomRequest = null, bool $includePending = true): void
    {
        if (($validated['status'] ?? RoomRequest::STATUS_PENDING) === RoomRequest::STATUS_REJECTED || ($validated['status'] ?? null) === RoomRequest::STATUS_CANCELLED) {
            return;
        }

        $activeSemester = Semester::where('is_active', true)->first();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $errors = $availabilityService->validateAvailability([
            'room_id' => $validated['room_id'],
            'request_date' => $validated['request_date'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'semester_id' => $activeSemester?->id,
            'academic_year_id' => $activeAcademicYear?->id,
        ], $includePending, null, $roomRequest?->id);

        if ($errors !== []) {
            throw ValidationException::withMessages(['room_id' => $errors[0]]);
        }
    }

    private function formData(RoomRequest $roomRequest): array
    {
        return [
            'roomRequest' => $roomRequest,
            'requesters' => User::with('roles')->orderBy('name')->get(),
            'rooms' => Room::where('is_active', true)->orderBy('code')->get(),
            'statuses' => RoomRequest::STATUSES,
            'canChooseRequester' => $this->currentUserIsAdmin(),
            'canEditStatus' => $this->currentUserIsAdmin(),
        ];
    }

    private function currentUserIsAdmin(): bool
    {
        return (bool) request()->user()?->isAn('admin');
    }

    private function canCreateRoomRequest(): bool
    {
        return (bool) request()->user()?->isAn('admin', 'dosen', 'ketua_kelas');
    }

    private function authorizeRoomRequestAccess(RoomRequest $roomRequest): void
    {
        if ($this->currentUserIsAdmin() || $roomRequest->requester_id === request()->user()->id) {
            return;
        }

        abort(403, 'Anda hanya dapat mengakses permintaan ruangan milik akun Anda.');
    }
}
