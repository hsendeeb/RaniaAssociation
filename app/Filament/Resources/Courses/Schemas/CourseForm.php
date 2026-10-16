<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('المعرّف (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('التصنيف')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('instructor_id')
                            ->label('المدرّب')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('summary')
                            ->label('الملخص')
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->label('صورة الغلاف')
                            ->image()
                            ->directory('courses')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make('تفاصيل الدورة')
                    ->columns(3)
                    ->components([
                        Select::make('level')
                            ->label('المستوى')
                            ->options(CourseLevel::class)
                            ->default(CourseLevel::Beginner->value)
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(CourseStatus::class)
                            ->default(CourseStatus::Draft->value)
                            ->required(),
                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        DatePicker::make('starts_at')
                            ->label('تاريخ البداية'),
                        DatePicker::make('ends_at')
                            ->label('تاريخ الانتهاء')
                            ->afterOrEqual('starts_at'),
                        TextInput::make('duration_hours')
                            ->label('عدد الساعات')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('capacity')
                            ->label('السعة (عدد الطلاب)')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_featured')
                            ->label('دورة مميّزة')
                            ->default(false),
                    ]),
            ]);
    }
}
