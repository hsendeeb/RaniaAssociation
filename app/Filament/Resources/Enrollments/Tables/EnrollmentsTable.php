<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('progress')
                    ->label('نسبة الإنجاز')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->date()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('تاريخ الإكمال')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(EnrollmentStatus::class),
                SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('الموافقة على التسجيل')
                    ->modalDescription('سيتم تحويل حالة التسجيل إلى «نشط» وتثبيت تاريخ التسجيل.')
                    ->visible(fn (Enrollment $record): bool => ! in_array(
                        $record->status,
                        [EnrollmentStatus::Active, EnrollmentStatus::Completed],
                        true,
                    ))
                    ->action(function (Enrollment $record): void {
                        $record->update([
                            'status' => EnrollmentStatus::Active,
                            'enrolled_at' => $record->enrolled_at ?? now(),
                        ]);
                    }),
                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء التسجيل')
                    ->visible(fn (Enrollment $record): bool => $record->status !== EnrollmentStatus::Cancelled)
                    ->action(fn (Enrollment $record) => $record->update(['status' => EnrollmentStatus::Cancelled])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('موافقة على المحدّد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (EloquentCollection $records) => $records->each(
                            fn (Enrollment $record) => $record->update([
                                'status' => EnrollmentStatus::Active,
                                'enrolled_at' => $record->enrolled_at ?? now(),
                            ]),
                        )),
                    BulkAction::make('cancel')
                        ->label('إلغاء المحدّد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (EloquentCollection $records) => $records->each(
                            fn (Enrollment $record) => $record->update(['status' => EnrollmentStatus::Cancelled]),
                        )),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
