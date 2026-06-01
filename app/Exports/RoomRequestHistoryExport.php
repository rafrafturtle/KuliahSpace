<?php

namespace App\Exports;

use App\Models\RoomRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoomRequestHistoryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $filters = [],
        private readonly ?User $user = null,
    ) {}

    public function collection(): Collection
    {
        return RoomRequest::with(['requester', 'room', 'approver'])
            ->whereIn('status', RoomRequest::HISTORY_STATUSES)
            ->when(! $this->user?->isAn('admin'), function (Builder $query): void {
                $this->user
                    ? $query->where('requester_id', $this->user->id)
                    : $query->whereRaw('1 = 0');
            })
            ->when($this->filters['start_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('request_date', '>=', $date))
            ->when($this->filters['end_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('request_date', '<=', $date))
            ->orderByDesc('request_date')
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Pengaju',
            'Ruangan',
            'Tanggal Pengajuan',
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
            'Keperluan',
            'Status',
            'Catatan Admin',
            'Disetujui/Ditolak Oleh',
            'Waktu Diproses',
        ];
    }

    public function map($roomRequest): array
    {
        return [
            $roomRequest->requester?->name ?? '-',
            $this->roomName($roomRequest),
            $roomRequest->request_date?->format('d M Y') ?? '-',
            RoomRequest::dayLabel($roomRequest->day_of_week),
            substr((string) $roomRequest->start_time, 0, 5),
            substr((string) $roomRequest->end_time, 0, 5),
            $roomRequest->purpose,
            RoomRequest::statusLabel($roomRequest->status),
            $roomRequest->admin_note ?? '-',
            $roomRequest->approver?->name ?? '-',
            $roomRequest->approved_at?->format('d M Y H:i') ?? '-',
        ];
    }

    private function roomName(RoomRequest $roomRequest): string
    {
        if (! $roomRequest->room) {
            return '-';
        }

        return trim($roomRequest->room->code.' - '.$roomRequest->room->name);
    }
}
