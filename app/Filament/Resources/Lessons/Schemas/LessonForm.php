<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الدرس')
                    ->columns(2)
                    ->components([
                        Select::make('course_id')
                            ->label('الدورة')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label('المحتوى')
                            ->columnSpanFull(),
                    ]),
                Section::make('الوسائط')
                    ->columns(2)
                    ->components([
                        TextInput::make('video_url')
                            ->label('رابط الفيديو')
                            ->url()
                            ->maxLength(255),
                        FileUpload::make('attachment')
                            ->label('المرفق')
                            ->directory('lessons'),
                    ]),
                Section::make('الإعدادات')
                    ->columns(3)
                    ->components([
                        TextInput::make('duration_minutes')
                            ->label('المدة (دقائق)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('position')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true),
                    ]),
            ]);
    }
}
