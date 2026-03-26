<?php

namespace App\Enums;

enum MovementType: string
{
    case RESERVED  = 'reserved';
    case RESTOCKED = 'restocked';
    case RELEASED  = 'released';

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => 'Зарезервовано',
            self::RESTOCKED => 'Поповнення від постачальника',
            self::RELEASED => 'Звільнення резерву',
        };
    }
}
