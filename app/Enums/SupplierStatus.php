<?php

namespace App\Enums;

enum SupplierStatus: string
{
    case OK = 'ok';
    case FAIL = 'fail';
    case DELAYED = 'delayed';

    public function label(): string
    {
        return match ($this) {
            self::OK => 'Підтверджено',
            self::FAIL => 'Відхилено',
            self::DELAYED => 'Затримано',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::OK, self::FAIL => true,
            self::DELAYED => false,
        };
    }

    public function needsRetry(): bool
    {
        return $this === self::DELAYED;
    }
}
