<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'code',
        'name',
        'building_id',
        'building',
        'floor',
        'capacity',
        'facilities',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function buildingRecord(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function roomRequests(): HasMany
    {
        return $this->hasMany(RoomRequest::class);
    }
}
