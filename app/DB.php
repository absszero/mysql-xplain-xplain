<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Jasny\MySQL\DB as JasnyDB;
use Symfony\Component\Console\Input\InputInterface;

class DB
{
    private $configure;

    public static $version;

    public function __construct($host = null, $user = null, $pass = null, $base = null)
    {
        $this->configure = compact('host', 'user', 'pass', 'base');

        if (!$host) {
            $file = __DIR__ . '/../conf/db.php';
            if (file_exists($file)) {
                $this->configure = array_merge($this->configure, require $file);
            }
        }
    }

    public function setUp(InputInterface $input = null)
    {
        if ($input) {
            $options = array_keys($this->configure);
            $options[] = 'sql-mode';
            foreach ($options as $option) {
                if ($value = $input->getOption($option)) {
                    $this->configure[$option] = $value;
                }
            }
        }

        $db = new JasnyDB(
            $this->configure['host'],
            $this->configure['user'],
            $this->configure['pass'],
            $this->configure['base']
        );

        if ($db->connect_errno) {
            throw new \Exception('Failed to connect to MySQL: ' . $db->connect_error);
        }

        self::$version = mb_substr($this->conn()->server_info, 0, 3);

        if (array_key_exists('sql-mode', $this->configure)) {
            $this->conn()->query("set sql_mode={$this->configure['sql-mode']}");
        }
    }

    public function __call($name, array $arguments)
    {
        $method = JasnyDB::class . "::$name";
        return call_user_func_array($method, $arguments);
    }
}
