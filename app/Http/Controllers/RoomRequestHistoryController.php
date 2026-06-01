<?php

namespace App\Http\Controllers;

use App\Exports\RoomRequestHistoryExport;
use App\Models\RoomRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoomRequestHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        return view('room-request-history.index', [
            'roomRequests' => $this->historyQuery($request, $filters)->get(),
            'filters' => $filters,
            'filterSummary' => $this->filterSummary($filters),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request);

        return Excel::download(
            new RoomRequestHistoryExport($filters, $request->user()),
            'riwayat-pengajuan-ruang.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $roomRequests = $this->historyQuery($request, $filters)->get();

        return Pdf::loadView('room-request-history.pdf', [
            'roomRequests' => $roomRequests,
            'filters' => $filters,
            'filterSummary' => $this->filterSummary($filters),
        ])
            ->setPaper('a4', 'landscape')
            ->download('riwayat-pengajuan-ruang.pdf');
    }

    private function historyQuery(Request $request, array $filters): Builder
    {
        return RoomRequest::with(['requester.roles', 'room', 'approver'])
            ->whereIn('status', RoomRequest::HISTORY_STATUSES)
            ->when(! $this->currentUserIsAdmin($request), fn (Builder $query) => $query->where('requester_id', $request->user()->id))
            ->when($filters['start_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('request_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('request_date', '<=', $date))
            ->orderByDesc('request_date')
            ->orderByDesc('created_at');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function filterSummary(array $filters): ?string
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if (! $startDate && ! $endDate) {
            return null;
        }

        return sprintf(
            '%s sampai %s',
            $startDate ? date('d M Y', strtotime($startDate)) : 'awal data',
            $endDate ? date('d M Y', strtotime($endDate)) : 'akhir data'
        );
    }

    private function currentUserIsAdmin(Request $request): bool
    {
        return (bool) $request->user()?->isAn('admin');
    }
}
