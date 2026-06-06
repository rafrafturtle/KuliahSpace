<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('floor')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->foreignUuid('building_id')
                ->nullable()
                ->after('building')
                ->constrained('buildings')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $this->backfillBuildingsFromRooms();
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('building_id');
        });

        Schema::dropIfExists('buildings');
    }

    private function backfillBuildingsFromRooms(): void
    {
        $now = now();

        DB::table('rooms')
            ->whereNotNull('building')
            ->where('building', '<>', '')
            ->select('building')
            ->distinct()
            ->orderBy('building')
            ->pluck('building')
            ->each(function (string $buildingName) use ($now): void {
                $floors = DB::table('rooms')
                    ->where('building', $buildingName)
                    ->whereNotNull('floor')
                    ->where('floor', '<>', '')
                    ->pluck('floor')
                    ->unique()
                    ->sort()
                    ->values()
                    ->implode(', ');

                $buildingId = DB::table('buildings')
                    ->where('name', $buildingName)
                    ->value('id');

                if (! $buildingId) {
                    $buildingId = Str::uuid()->toString();

                    DB::table('buildings')->insert([
                        'id' => $buildingId,
                        'name' => $buildingName,
                        'code' => null,
                        'floor' => $floors !== '' ? $floors : null,
                        'description' => null,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('rooms')
                    ->where('building', $buildingName)
                    ->whereNull('building_id')
                    ->update([
                        'building_id' => $buildingId,
                        'updated_at' => $now,
                    ]);
            });
    }
};
