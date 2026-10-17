<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Blank phone numbers must be NULL (not an empty string) so that the
        // unique index below does not collide on rows without a phone.
        DB::table('students')->where('phone', '')->update(['phone' => null]);

        // Keep the earliest record for each duplicated phone number and clear
        // the rest, so the unique index can be created on existing data.
        $duplicates = DB::table('students')
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('count(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            $keepId = DB::table('students')->where('phone', $phone)->min('id');

            DB::table('students')
                ->where('phone', $phone)
                ->where('id', '!=', $keepId)
                ->update(['phone' => null]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
