<?php

namespace App\Enum;


class TypeEnum 
{
    const BONUS = 0;
    const PENALTY = 1;
    const CONSUMPTION = 2;

    public static function get(): array
    {
        $enum = array();

        $enum['bonus'] = self::BONUS;
        $enum['penalty'] = self::PENALTY;
        $enum['consumption'] = self::CONSUMPTION;

        return $enum;
    }
}
