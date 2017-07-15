<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Jasny\MySQL\DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExplainCommand extends Command
{
    private $io;
    private $version;

    protected function configure()
    {
        $this->setName("explain")
                ->setDescription("explain a SQL file")
                ->addArgument('query', InputArgument::REQUIRED, 'The SQL query or SQL file')
                ->addOption('host', 'd', InputOption::VALUE_OPTIONAL, 'The host name')
                ->addOption('base', 'b', InputOption::VALUE_OPTIONAL, 'The database')
                ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'The user name')
                ->addOption('pass', 'p', InputOption::VALUE_OPTIONAL, 'The password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initStyles($input, $output);
        $this->setUpDatabase($input);

        $query = $input->getFirstArgument();
        if (file_exists($query)) {
            $file = new \SplFileObject($query);
            while (!$file->eof()) {
                $query = $file->fgets();
                $this->explain($query);
            }
        } else {
            $this->explain($query);
        }
    }

    private function explain($query)
    {
        $results = $this->query($query);
        if (!$results) {
            return;
        }

        $explainer = new Explainer($query, $this->version);
        foreach ($results as $result) {
            $explainer->addRow(new Row($result, null, $explainer));
        }

        $outputer = new Outputer($explainer, $this->io);
        $outputer->render();
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
        $style = new OutputFormatterStyle('red', 'cyan');
        $output->getFormatter()->setStyle('code', $style);

        $this->io = new SymfonyStyle($input, $output);
    }
}
