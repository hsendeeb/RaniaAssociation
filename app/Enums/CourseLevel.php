<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CourseLevel: string implements HasLabel
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::Beginner => 'مبتدئ',
            self::Intermediate => 'متوسط',
            self::Advanced => 'متقدم',
        };
    }
}
