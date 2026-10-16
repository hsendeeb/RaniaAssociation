<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('الغلاف'),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->badge()
                    ->sortable(),
                TextColumn::make('instructor.name')
                    ->label('المدرّب')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('lessons_count')
                    ->label('الدروس')
                    ->counts('lessons')
                    ->badge(),
                TextColumn::make('enrollments_count')
                    ->label('المسجّلون')
                    ->counts('enrollments')
                    ->badge(),
                IconColumn::make('is_featured')
                    ->label('مميّزة')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CourseStatus::class),
                SelectFilter::make('level')
                    ->label('المستوى')
                    ->options(CourseLevel::class),
                SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_featured')
                    ->label('دورة مميّزة'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
