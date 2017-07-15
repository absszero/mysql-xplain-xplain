<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Symfony\Component\Console\Helper\Table as HelperTable;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;

class Outputer
{
    private $explainer;
    private $io;
    private $noANSI;

    /**
     * summary
     */
    public function __construct(Explainer $explainer, $input, $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->explainer = $explainer;
        $this->io = new SymfonyStyle($input, $output);
        $this->noANSI = $input->getOption('no-ansi');
    }

    public function render()
    {
        if (!$this->explainer->rows) {
            return;
        }

        $query = $this->explainer->getQuery();
        if (!$this->noANSI) {
            $query = \SqlFormatter::format($query);
        }

        $this->io->section('Result');
        $table = new HelperTable($this->output);
        $headers = $this->getHeaders();
        $table->setHeaders($headers);

        $rows = $this->getRows($query, count($headers));
        $table->setRows($rows);
        $table->render();

        $this->io->newline();
        $this->io->section('Hint');
        $this->io->listing($this->explainer->hints);
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

        foreach ($this->explainer->rows as $row) {
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

        return $rows;
    }

    public function getHeaders()
    {
        return array_keys($this->explainer->header_row);
    }
}
