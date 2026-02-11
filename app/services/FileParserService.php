<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class FileParserService
{
    public function parse(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->parseCsv($path),
            'xlsx' => $this->parseXlsx($path),
            default => throw new RuntimeException('Unsupported file type.'),
        };
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Could not read CSV file.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(static fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);

        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Invalid XLSX file.');
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($worksheetXml === false) {
            throw new RuntimeException('Worksheet not found in XLSX.');
        }

        $sharedStrings = [];
        if ($sharedStringsXml !== false) {
            $shared = new SimpleXMLElement($sharedStringsXml);
            foreach ($shared->si as $si) {
                $sharedStrings[] = (string) ($si->t ?? '');
            }
        }

        $sheet = new SimpleXMLElement($worksheetXml);
        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        foreach ($sheet->xpath('//x:sheetData/x:row') as $rowNode) {
            $row = [];
            foreach ($rowNode->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $value = (string) ($cell->v ?? '');
                $row[] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : $value;
            }
            $rows[] = array_map(static fn ($value) => trim((string) $value), $row);
        }

        return $rows;
    }
}
