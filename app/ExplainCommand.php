<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Jasny\MySQL\DB;
use Jasny\MySQL\DB_Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExplainCommand extends Command
{
    private $version;

    protected function configure()
    {
        $this->setName("explain")
                ->setDescription("Explain a SQL query or SQL file")
                ->addArgument('query', InputArgument::REQUIRED, 'The SQL query or SQL file')
                ->addOption('host', 'd', InputOption::VALUE_OPTIONAL, 'The host name')
                ->addOption('base', 'b', InputOption::VALUE_OPTIONAL, 'The database')
                ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'The user name')
                ->addOption('pass', 'p', InputOption::VALUE_OPTIONAL, 'The password')
                ->addOption('danger', 'g', InputOption::VALUE_NONE, 'Output only danger queries')
                ->addOption('no-hint', null, InputOption::VALUE_NONE, 'Disable hint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initStyles($input, $output);
        $this->setUpDatabase($input);

        $query = $input->getFirstArgument();
        if (file_exists($query)) {
            $file = new \SplFileObject($query);
            while (!$file->eof()) {
                $query = trim($file->fgets());
                $this->explain($query);
            }
        } else {
            $this->explain($query);
        }
    }

    private function explain($query)
    {
        try {
            $results = $this->query($query);
            if (!$results) {
                return;
            }

            $explainer = new Explainer($query, $this->version);

            $table = new Table($query);
            $tables = $table->getTables();

            foreach ($results as $result) {
                $explainer->addRow(new Row($result, $explainer, $tables));
            }

            $outputer = new Outputer($explainer);
            $outputer->render();
        } catch (DB_Exception $e) {
            IO::write("<error>{$e->getError()}</error>: " . SQLDecorator::highlight($e->getQuery()));
            IO::newline();
        }
    }

    private function setUpDatabase(InputInterface $input)
    {
        $configure = [];
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

        new DB(
            $configure['host'],
            $configure['user'],
            $configure['pass'],
            $configure['base']
        );

        $this->version = mb_substr(DB::conn()->server_info, 0, 3);
    }

    private function isSelectSQL($sql)
    {
        $sql = ltrim($sql);
        return (0 === strpos(strtolower($sql), 'select'));
    }

    private function query($sql)
    {
        if (!$this->isSelectSQL($sql)) {
            return false;
        }

        if (false === strpos(strtolower($sql), 'explain')) {
            $sql = "EXPLAIN $sql";
        }

        return DB::conn()->fetchAll($sql);
    }

    public function initStyles(InputInterface $input, OutputInterface $output)
    {
        SQLDecorator::$ansi = !$input->getOption('no-ansi');

        $style = new OutputFormatterStyle('red', 'cyan');
        $output->getFormatter()->setStyle('code', $style);
        IO::setIO($input, $output);
    }
}
