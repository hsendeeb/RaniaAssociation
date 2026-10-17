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
        Schema::table('students', function (Blueprint $table) {
            $table->string('phone_country', 2)->nullable()->after('phone');
        });

        // تحويل الأرقام المخزّنة إلى صيغة E.164 الموحّدة ونسبة الدولة الافتراضية.
        $default = PhoneNumber::defaultCountry();

        foreach (DB::table('students')->orderBy('id')->get(['id', 'phone']) as $student) {
            if (blank($student->phone)) {
                continue;
            }

            $e164 = PhoneNumber::toE164($student->phone, $default);

            DB::table('students')->where('id', $student->id)->update([
                'phone' => $e164,
                'phone_country' => PhoneNumber::countryFor($e164) ?? $default,
            ]);
        }

        // بعد التحويل قد تتطابق أرقام كانت مختلفة الصيغة؛ نُبقي الأقدم ونُفرّغ الباقي.
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('phone_country');
        });
    }
};
