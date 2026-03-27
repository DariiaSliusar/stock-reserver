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

    public function isTerminal(): bool
    {
        return match($this) {
            self::RESERVED, self::FAILED => true,
            default => false,
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::PENDING => in_array($next, [self::RESERVED, self::AWAITING_RESTOCK, self::FAILED]),
            self::AWAITING_RESTOCK => in_array($next, [self::RESERVED, self::FAILED]),
            self::RESERVED,
            self::FAILED => false,
        };
    }

}
