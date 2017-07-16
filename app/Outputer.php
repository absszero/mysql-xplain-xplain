<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Symfony\Component\Console\Helper\Table as HelperTable;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;

class Outputer
{
    private $explainer;
    private $onlyDanger;
    private $onlyWarning;
    private $stderrQueries;

    /**
     * summary
     */
    public function __construct(Explainer $explainer)
    {
        $this->explainer = $explainer;
        $this->onlyDanger = IO::$input->getOption('danger');
        $this->onlyWarning = IO::$input->getOption('warning');
        $this->stderrQueries = IO::$input->getOption('stderr');
    }

    public function render()
    {
        if (!$this->explainer->rows) {
            return;
        }

        $query = SQLDecorator::format($this->explainer->getQuery());
        $rows = $this->getRows();
        if ($rows) {
            $output = IO::$output;
            foreach (['danger', 'warning'] as $type) {
                if ($rows[$type] and in_array($type, $this->stderrQueries)) {
                    $output = IO::getErrorOutput();
                }
            }

            $table = new HelperTable($output);
            $headers = $this->getHeaders();
            $table->setHeaders($headers);

            $queryRow = $this->getQueryRow($query, count($headers));
            $rows = array_merge($queryRow, $rows['rows']);
            $table->setRows($rows);

            $table->render();
            IO::newline();

            if (!IO::$input->getOption('no-hint')) {
                IO::listing($this->explainer->hints);
            }
        }
    }

    public function getQueryRow($query, $colspan)
    {
        return [
            [new TableCell($query, ['colspan' => $colspan])],
            new TableSeparator(),
        ];
    }

    public function getRows()
    {
        $rows = array();

        $danger = 0;
        $warning = 0;
        foreach ($this->explainer->rows as $row) {
            $danger += $row->isDanger();
            $warning += $row->isWarning();

            $cols = [];
            foreach ($row->cells as $cell) {
                $style = '%s';
                if ($cell->isDanger()) {
                    $style = "<error>%s</error>";
                } elseif ($cell->isSuccess()) {
                    $style = "<info>%s</info>";
                } elseif ($cell->isWarning()) {
                    $style = '<comment>%s</comment>';
                }

                $cols[] = sprintf($style, $cell->v);
            }
            $rows[] = $cols;
        }

        if ($this->onlyWarning or $this->onlyDanger) {
            if ($this->onlyWarning and 0 === $warning) {
                $rows = false;
            }

            if ($this->onlyDanger and 0 === $danger) {
                $rows = false;
            }
        }

        return compact('rows', 'danger', 'warning');
    }

    public function getHeaders()
    {
        return array_keys($this->explainer->header_row);
    }
}
