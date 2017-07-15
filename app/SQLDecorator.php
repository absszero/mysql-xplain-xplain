<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use SqlFormatter;

class SQLDecorator
{
    public static $ansi = true;

    public static function highlight($query)
    {
        if (self::$ansi) {
            $query =  SqlFormatter::highlight($query);
        }

        return $query;
    }

    public static function format($query, $highlight = true)
    {
        return SqlFormatter::format($query, self::$ansi);
    }
}
