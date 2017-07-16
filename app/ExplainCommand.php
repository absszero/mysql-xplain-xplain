<?php

namespace Rap2hpoutre\MySQLExplainExplain;

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
    protected function configure()
    {
        $this->setName("explain")
                ->setDescription("Explain a SQL query or SQL file")
                ->addArgument('query', InputArgument::REQUIRED, 'The SQL query or SQL file')
                ->addOption('host', 'o', InputOption::VALUE_OPTIONAL, 'The host name')
                ->addOption('base', 'b', InputOption::VALUE_OPTIONAL, 'The database')
                ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'The user name')
                ->addOption('pass', 'p', InputOption::VALUE_OPTIONAL, 'The password')
                ->addOption('sql-mode', 's', InputOption::VALUE_OPTIONAL, 'The SQL Mode')
                ->addOption('danger', 'd', InputOption::VALUE_NONE, 'Output danger queries')
                ->addOption('warning', 'w', InputOption::VALUE_NONE, 'Output warning queries')
                ->addOption('no-hint', null, InputOption::VALUE_NONE, 'Disable hint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->db = new DB();
        $this->db->setUp($input);

        $this->initStyles($input, $output);

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
        if (empty($query)) {
            return;
        }

        try {
            $results = $this->query($query);
            if (!$results) {
                throw new DB_Exception('no result', 0, $query);
            }

            $explainer = new Explainer($query);

            $table = new Table($query);
            $tables = $table->getTables();

            foreach ($results as $result) {
                $explainer->addRow(new Row($result, $explainer, $tables));
            }

            $outputer = new Outputer($explainer);
            $outputer->render();
        } catch (DB_Exception $e) {
            IO::write("<error>{$e->getError()}</error>: " . SQLDecorator::highlight($e->getQuery()));
            IO::newline(2);
        }
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

        return $this->db->conn()->fetchAll($sql);
    }

    public function initStyles(InputInterface $input, OutputInterface $output)
    {
        SQLDecorator::$ansi = !$input->getOption('no-ansi');

        $style = new OutputFormatterStyle('cyan');
        $output->getFormatter()->setStyle('code', $style);
        IO::setIO($input, $output);
    }
}
