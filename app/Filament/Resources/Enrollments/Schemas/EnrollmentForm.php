<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Enums\EnrollmentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('أطراف التسجيل')
                    ->columns(2)
                    ->components([
                        Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('course_id')
                            ->label('الدورة')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('حالة التسجيل')
                    ->columns(3)
                    ->components([
                        Select::make('status')
                            ->label('الحالة')
                            ->options(EnrollmentStatus::class)
                            ->default(EnrollmentStatus::Pending->value)
                            ->required(),
                        TextInput::make('progress')
                            ->label('نسبة الإنجاز')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0),
                        DatePicker::make('enrolled_at')
                            ->label('تاريخ التسجيل')
                            ->default(now()),
                        DatePicker::make('completed_at')
                            ->label('تاريخ الإكمال')
                            ->afterOrEqual('enrolled_at'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
