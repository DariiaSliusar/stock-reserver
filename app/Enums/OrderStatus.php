<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case RESERVED = 'reserved';
    case AWAITING_RESTOCK = 'awaiting_restock';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Очікується',
            self::RESERVED => 'Зарезервовано',
            self::AWAITING_RESTOCK => 'Очікує поповнення',
            self::FAILED => 'Помилка',
        };
    }


}
