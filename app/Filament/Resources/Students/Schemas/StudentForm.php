<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50),
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
