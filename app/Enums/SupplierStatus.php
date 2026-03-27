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
}
