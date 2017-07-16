<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use PhpMyAdmin\SqlParser\Utils\Formatter;
use SqlFormatter;

class SQLDecorator
{
    public static $ansi = true;

    public static function highlight($query)
    {
        $option = array(
            'indentation' => false,
            'remove_comments' => false,
            'clause_newline' => false,
            'indent_parts' => false,
        );
        return self::format($query, $option);
    }

    public static function format($query, $extraOption = array())
    {
        $wrapper = '%s';
        $option = array(
            'parts_newline' => false,
        );
        if (!self::$ansi) {
            $option['type'] ='text';
        }

        if (php_sapi_name() !== 'cli') {
            $option['type'] = 'html';
            $wrapper = '<pre style="color: black; background-color: white;">%s</pre>';
        }

        $option = array_merge($option, $extraOption);

        return sprintf($wrapper, Formatter::format($query, $option));
    }
}
