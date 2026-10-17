<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * قائمة الدول بصيغة [الرمز => ['name' => ..., 'dial' => ...]].
     *
     * @return array<string, array{name: string, dial: string}>
     */
    public static function countries(): array
    {
        /** @var array<string, array{name: string, dial: string}> $list */
        $list = config('countries.list', []);

        return $list;
    }

    /**
     * رموز الدول المتاحة (ISO 3166-1 alpha-2).
     *
     * @return list<string>
     */
    public static function countryCodes(): array
    {
        return array_keys(self::countries());
    }

    /**
     * الدولة الافتراضية لإدخال أرقام الهاتف.
     */
    public static function defaultCountry(): string
    {
        return (string) config('countries.default', 'LB');
    }

    /**
     * رمز الاتصال الدولي لدولة محددة.
     */
    public static function dial(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        return self::countries()[$country]['dial'] ?? null;
    }

    /**
     * قائمة الدول مرتّبة (الدولة الافتراضية أولًا) لعناصر الاختيار في الواجهة.
     *
     * @return list<array{code: string, name: string, dial: string}>
     */
    public static function options(): array
    {
        $list = self::countries();
        $default = self::defaultCountry();

        $ordered = [];

        if (isset($list[$default])) {
            $ordered[$default] = $list[$default];
        }

        foreach ($list as $code => $row) {
            if ($code !== $default) {
                $ordered[$code] = $row;
            }
        }

        $options = [];

        foreach ($ordered as $code => $row) {
            $options[] = ['code' => $code, 'name' => $row['name'], 'dial' => $row['dial']];
        }

        return $options;
    }

    /**
     * خيارات جاهزة لقوائم Filament بصيغة [الرمز => «الاسم (+الرمز)»].
     *
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        $options = [];

        foreach (self::options() as $country) {
            $options[$country['code']] = $country['name'].' (+'.$country['dial'].')';
        }

        return $options;
    }

    /**
     * تحويل رقم هاتف مُدخل إلى صيغة E.164 الموحّدة (‎+96171994952‎).
     *
     * - إذا بدأ الرقم بـ «+» أو «00» فسيُعامل كرقم دولي كامل ويُعتمد كما هو.
     * - وإلا فسيُعامل كرقم وطني وتُضاف إليه بادئة اتصال الدولة المختارة،
     *   مع إزالة صفر النداء الوطني (أو رمز الدولة إن كتبه المستخدم).
     */
    public static function toE164(?string $value, ?string $country = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        $isInternational = str_starts_with($value, '+') || str_starts_with($value, '00');

        if ($isInternational) {
            if (str_starts_with($digits, '00')) {
                $digits = substr($digits, 2);
            }

            $digits = ltrim($digits, '0');

            return $digits === '' ? null : '+'.$digits;
        }

        $dial = self::dial($country) ?? self::dial(self::defaultCountry());

        $national = ltrim($digits, '0');

        // قد يكتب المستخدم رمز الدولة دون علامة «+»؛ نُزيله لتجنّب تكراره.
        if (
            $dial !== null
            && ! str_starts_with($digits, '0')
            && str_starts_with($national, $dial)
            && strlen($national) - strlen($dial) >= 6
        ) {
            $national = substr($national, strlen($dial));
        }

        if ($national === '') {
            return null;
        }

        return $dial === null ? '+'.$national : '+'.$dial.$national;
    }

    /**
     * الرقم الوطني فقط (بدون رمز الدولة) لعرضه في النماذج.
     */
    public static function national(?string $value, ?string $country = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        $country ??= self::countryFor($digits);
        $dial = self::dial($country);

        if ($dial !== null && $dial !== '' && str_starts_with($digits, $dial)) {
            return substr($digits, strlen($dial));
        }

        return $digits;
    }

    /**
     * استنتاج رمز الدولة من رقم بصيغة دولية عبر أطول مطابقة لرمز الاتصال.
     *
     * ملاحظة: الرموز المشتركة (مثل ‎+1‎) قد تُنسب لأول دولة في القائمة.
     */
    public static function countryFor(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        $match = null;
        $matchLength = 0;

        foreach (self::countries() as $code => $row) {
            if ($row['dial'] !== '' && str_starts_with($digits, $row['dial']) && strlen($row['dial']) > $matchLength) {
                $match = $code;
                $matchLength = strlen($row['dial']);
            }
        }

        return $match;
    }

    /**
     * تحقّق مبسّط من صيغة E.164 (من 7 إلى 15 رقمًا).
     */
    public static function isValidE164(?string $value): bool
    {
        return is_string($value) && preg_match('/^\+[1-9]\d{6,14}$/', $value) === 1;
    }

    /**
     * توحيد الرقم إلى أرقام وطنية بلا رمز دولة.
     *
     * تُستخدم هذه الصيغة في ترحيل سابق، لذا تبقى متاحة للتوافق.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '961') && strlen($digits) > 8) {
            $digits = substr($digits, 3);
        }

        $digits = ltrim($digits, '0');

        return $digits === '' ? null : $digits;
    }
}
