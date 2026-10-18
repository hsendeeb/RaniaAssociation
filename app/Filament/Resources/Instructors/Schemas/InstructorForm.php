<?php

namespace App\Filament\Resources\Instructors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الشخصية')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('specialization')
                            ->label('التخصص')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50),
                        Textarea::make('bio')
                            ->label('نبذة تعريفية')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('الصورة والحالة')
                    ->columns(2)
                    ->components([
                        FileUpload::make('avatar')
                            ->label('الصورة الشخصية')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->directory('instructors')
                            ->imageEditor(),
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                    ]),
            ]);
    }
}
