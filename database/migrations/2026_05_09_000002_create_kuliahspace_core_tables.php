<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedInteger('capacity');
            $table->text('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credits')->nullable();
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('lecturer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('class_name');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('week_number')->nullable();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['room_id', 'day_of_week', 'semester_id', 'academic_year_id', 'is_active'], 'class_schedule_conflict_index');
        });

        Schema::create('room_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('request_date');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('purpose');
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'request_date', 'status'], 'room_request_conflict_index');
        });

        Schema::create('course_class_leaders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('lecturer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->unique(
                ['course_id', 'lecturer_id', 'semester_id', 'academic_year_id'],
                'course_class_leader_unique_assignment'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_class_leaders');
        Schema::dropIfExists('room_requests');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('rooms');
    }
};
