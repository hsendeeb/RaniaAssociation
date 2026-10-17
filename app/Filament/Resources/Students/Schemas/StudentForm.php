<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Rules\UniqueStudentPhone;
use App\Support\PhoneNumber;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الطالب')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Select::make('gender')
                            ->label('الجنس')
                            ->options(Gender::class),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                        Select::make('phone_country')
                            ->label('الدولة')
                            ->options(PhoneNumber::selectOptions())
                            ->searchable()
                            ->default(PhoneNumber::defaultCountry())
                            ->formatStateUsing(fn (?string $state): string => $state ?: PhoneNumber::defaultCountry()),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50)
                            ->helperText('أدخل الرقم دون رمز الدولة.')
                            ->formatStateUsing(fn (?string $state): ?string => PhoneNumber::national($state))
                            ->dehydrateStateUsing(fn (?string $state, Get $get): ?string => PhoneNumber::toE164($state, $get('phone_country')))
                            ->rule(static function (Field $component, Get $get): UniqueStudentPhone {
                                $record = $component->getRecord();

                                return new UniqueStudentPhone(
                                    $get('phone_country'),
                                    $record instanceof Student ? $record->getKey() : null,
                                );
                            }),
                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->maxDate(now()),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(StudentStatus::class)
                            ->default(StudentStatus::Active->value)
                            ->required(),
                    ]),
                Section::make('بيانات ولي الأمر')
                    ->columns(2)
                    ->components([
                        TextInput::make('guardian_name')
                            ->label('اسم ولي الأمر')
                            ->maxLength(255),
                        TextInput::make('guardian_phone')
                            ->label('هاتف ولي الأمر')
                            ->tel()
                            ->maxLength(50),
                    ]),
                Section::make('الحساب والملاحظات')
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label('حساب المستخدم المرتبط')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('اربط الطالب بحساب مستخدم إن كان لديه حساب في المنصة.'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
