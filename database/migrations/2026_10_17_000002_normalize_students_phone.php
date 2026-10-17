<?php

use App\Support\PhoneNumber;
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
        // نُزيل القيد الفريد مؤقتًا للسماح بتعديل الأرقام أثناء التوحيد.
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });

        foreach (DB::table('students')->orderBy('id')->get(['id', 'phone']) as $student) {
            DB::table('students')
                ->where('id', $student->id)
                ->update(['phone' => PhoneNumber::normalize($student->phone)]);
        }

        // بعد التوحيد قد تتطابق أرقام مختلفة الصيغة؛ نُبقي الأقدم ونُفرّغ الباقي.
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
        // لا يمكن التراجع عن توحيد صيغة الأرقام.
    }
};
