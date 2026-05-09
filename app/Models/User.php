<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRolesAndAbilities, HasUuidPrimaryKey, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'lecturer_id');
    }

    public function roomRequests(): HasMany
    {
        return $this->hasMany(RoomRequest::class, 'requester_id');
    }

    public function approvedRoomRequests(): HasMany
    {
        return $this->hasMany(RoomRequest::class, 'approved_by');
    }

    public function studentClassLeaderAssignments(): HasMany
    {
        return $this->hasMany(CourseClassLeader::class, 'student_id');
    }

    public function lecturerClassLeaderAssignments(): HasMany
    {
        return $this->hasMany(CourseClassLeader::class, 'lecturer_id');
    }
}
