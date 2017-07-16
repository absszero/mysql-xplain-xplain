<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Jasny\MySQL\DB as JasnyDB;
use Symfony\Component\Console\Input\InputInterface;

class DB
{
    public static $version;

    public function setUp(InputInterface $input)
    {
        $configure = array();
        $file = __DIR__ . '/../conf/db.php';
        if (file_exists($file)) {
            $configure = require $file;
        }

        foreach (['host', 'base','user', 'pass'] as $field) {
            if (!array_key_exists($field, $configure)) {
                $configure[$field] = null;
            }
            if ($value = $input->getOption($field)) {
                $configure[$field] = $value;
            }
        }

        new JasnyDB(
            $configure['host'],
            $configure['user'],
            $configure['pass'],
            $configure['base']
        );

        self::$version = mb_substr($this->conn()->server_info, 0, 3);
    }

    public function __call($name, array $arguments)
    {
        $method = JasnyDB::class . "::$name";
        return call_user_func_array($method, $arguments);
    }
}
