<?php

namespace App\Enums;

enum SupplierStatus: string
{
    case OK = 'ok';
    case FAIL = 'fail';
    case DELAYED = 'delayed';


}
