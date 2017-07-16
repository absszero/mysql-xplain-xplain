<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IO
{
    private static $style;
    public static $input;
    public static $output;

    public static function setIO(InputInterface $input, OutputInterface $output)
    {
        self::$input = $input;
        self::$output = $output;
        self::$style = new SymfonyStyle($input, $output);
    }

    public static function __callStatic($name, array $arguments)
    {
        return call_user_func_array(array(self::$style,$name), $arguments);
    }

    public static function getErrorOutput()
    {
        return self::$output instanceof ConsoleOutputInterface ? self::$output->getErrorOutput() : self::$output;
    }
}
