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

    /**
     * summary
     */
    public function __construct(Explainer $explainer)
    {
        $this->explainer = $explainer;
        $this->onlyDanger = IO::$input->getOption('danger');
    }

    public function render()
    {
        if (!$this->explainer->rows) {
            return;
        }

        $table = new HelperTable(IO::$output);
        $headers = $this->getHeaders();
        $table->setHeaders($headers);

        $query = SQLDecorator::format($this->explainer->getQuery());
        $rows = $this->getRows($query, count($headers));
        if ($rows) {
            $table->setRows($rows);
            $table->render();
            IO::newline();

            if (!IO::$input->getOption('no-hint')) {
                IO::newline();
                IO::section('Hint');
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

    public function getRows($query, $colspan)
    {
        $rows = $this->getQueryRow($query, $colspan);


        $danger = 0;
        foreach ($this->explainer->rows as $row) {
            $cols = [];
            foreach ($row->cells as $cell) {
                $style = '%s';
                if ($danger += $cell->isDanger()) {
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

        if ($this->onlyDanger and 0 === $danger) {
            $rows = false;
        }

        return $rows;
    }

    public function getHeaders()
    {
        return array_keys($this->explainer->header_row);
    }
}
