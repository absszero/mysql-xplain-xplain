<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;

class Table
{
    private $query;

    /**
     * summary
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function getTables()
    {
        $tables = [];
        $parser = new Parser($this->query);

        foreach ($parser->statements as $statement) {
            $tables = $this->parseStatement($statement);

            foreach ($statement->union as $union) {
                $tables = array_merge($tables, $this->parseStatement($union[1]));
            }
        }

        return $tables;
    }

    private function parseStatement(SelectStatement $statement)
    {
        $tables = [];
        $sources = ['expr', 'from', 'where', 'group', 'having', 'order', 'join'];
        foreach ($sources as $source) {
            $tables = array_merge($tables, $this->findTables($statement->$source));
        }

        return $tables;
    }

    private function findTables($items)
    {
        $tables = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                $tables = array_merge($tables, $this->findTables($item));
            }
        }

        if (is_object($items)) {
            if (property_exists($items, 'expr')) {
                $tables = array_merge($tables, $this->findTables($items->expr));
            }

            if (property_exists($items, 'alias') and $items->alias) {
                $tables[$items->alias] = $items->table;
            }
        }

        return $tables;
    }
}
