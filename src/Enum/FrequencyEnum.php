<?php

namespace App\Enum;


class FrequencyEnum 
{
    public static function get(): array
    {
        $enum = array();
        
        $enum['daily'] = 0;
        $enum['weekly'] = 1;
        $enum['always'] = 2;

        return $enum;
    }
}
