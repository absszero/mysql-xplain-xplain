<?php

namespace Rap2hpoutre\MySQLExplainExplain;

use Symfony\Component\Console\Style\SymfonyStyle;

class Outputer
{
    private $explainer;
    private $io;
    private $noANSI;

    /**
     * summary
     */
    public function __construct(Explainer $explainer, SymfonyStyle $io, $noANSI)
    {
        $this->explainer = $explainer;
        $this->io = $io;
        $this->noANSI = $noANSI;
    }

    public function render()
    {
        if (!$this->explainer->rows) {
            return;
        }

        $this->io->section('Query');
        $query = $this->explainer->getQuery();
        if (!$this->noANSI) {
            $query = \SqlFormatter::highlight($query);
        }
        $this->io->write($query);
        $this->io->section('Result');
        $this->io->table($this->getHeader(), $this->getBody());

        $this->io->section('Hint');
        $this->io->listing($this->explainer->hints);
    }

    public function getBody()
    {
        $body = [];

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

            $body[] = $cols;
        }

        return $body;
    }

    public function getHeader()
    {
        return array_keys($this->explainer->header_row);
    }
}
