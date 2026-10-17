<?php

namespace App\Rules;

use App\Models\Student;
use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * يتأكّد من أن رقم الهاتف غير مستخدم من قِبل طالب آخر، بعد تحويله إلى الصيغة
 * الدولية E.164 اعتمادًا على الدولة المُختارة، حتى لا تختلف الصيغ المتكافئة.
 */
class UniqueStudentPhone implements ValidationRule
{
    public function __construct(
        protected ?string $country = null,
        protected ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $e164 = PhoneNumber::toE164(is_string($value) ? $value : null, $this->country);

        if ($e164 === null) {
            return;
        }

        $query = Student::query()->where('phone', $e164);

        if ($this->ignoreId !== null) {
            $query->whereKeyNot($this->ignoreId);
        }

        if ($query->exists()) {
            $fail('رقم الهاتف مسجّل مسبقًا لطالب آخر.');
        }
    }
}
