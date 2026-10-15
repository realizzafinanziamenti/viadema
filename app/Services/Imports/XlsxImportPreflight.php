<?php

namespace App\Services\Imports;

use Closure;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Throwable;
use XMLReader;
use ZipArchive;

final class XlsxImportPreflight
{
    // Keep aligned with PracticesImport::chunkSize() and LeadsImport::chunkSize().
    public const CHUNK_SIZE = 100;

    private const MAIN_NAMESPACES = [
        'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
        'http://purl.oclc.org/ooxml/spreadsheetml/main',
    ];

    /** @return array<int, array<string, int|string>> */
    public function inspect(string $filename): array
    {
        $zip = new ZipArchive;
        $opened = false;

        try {
            if ($zip->open($filename) !== true) {
                $this->reject('Il file non è un documento Excel XLSX valido.');
            }
            $opened = true;

            $bytes = 0;
            if ($zip->numFiles > config('imports.max_zip_entries')) {
                $this->reject('Il file Excel contiene troppi elementi.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ((str_ends_with($stat['name'], '.xml') || str_ends_with($stat['name'], '.rels'))
                    && $stat['size'] > config('imports.max_xml_bytes')) {
                    $this->reject('Il file Excel contiene XML troppo grande.');
                }
                $bytes += $stat['size'];
                if ($bytes > config('imports.max_uncompressed_bytes')) {
                    $this->reject('Il file Excel è troppo grande una volta decompresso.');
                }
            }

            $rootRelations = $this->relationships($zip, '_rels/.rels', '');
            $workbook = null;
            foreach ($rootRelations as $relation) {
                if (str_ends_with($relation['type'], '/officeDocument')) {
                    if ($workbook !== null) {
                        $this->reject('Il file Excel contiene più documenti e non può essere importato.');
                    }
                    $workbook = $relation['path'];
                }
            }
            if ($workbook === null) {
                $this->reject('La struttura del file Excel non è valida.');
            }

            $directory = dirname($workbook) === '.' ? '' : dirname($workbook).'/';
            $relations = $this->relationships($zip, $directory.'_rels/'.basename($workbook).'.rels', $workbook);
            $sheets = [];
            $this->readXml($zip, $workbook, function (XMLReader $xml) use (&$sheets, $relations) {
                if ($xml->nodeType !== XMLReader::ELEMENT || $xml->localName !== 'sheet' || ! in_array($xml->namespaceURI, self::MAIN_NAMESPACES, true)) {
                    return;
                }
                $id = $xml->getAttributeNs('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
                    ?? $xml->getAttributeNs('id', 'http://purl.oclc.org/ooxml/officeDocument/relationships');
                $name = $xml->getAttribute('name');
                if ($name === null || $name === '' || isset($sheets[$name]) || ! isset($relations[$id]) || ! str_ends_with($relations[$id]['type'], '/worksheet')) {
                    $this->reject('Il file contiene un foglio non supportato o non valido.');
                }
                $sheets[$name] = $relations[$id]['path'];
            });

            if ($sheets === []) {
                $this->reject('Il file Excel non contiene fogli importabili.');
            }

            $info = [];
            $chunks = 0;
            foreach ($sheets as $name => $entry) {
                $sheet = $this->worksheetInfo($zip, $entry);
                $chunks += (int) ceil(max(0, $sheet['totalRows'] - 1) / self::CHUNK_SIZE);
                if ($chunks > config('imports.max_chunks') || config('imports.max_chunks') < 1) {
                    $this->reject('Il file Excel richiede troppi blocchi di elaborazione. Riduci le righe utilizzate o suddividi il file.');
                }
                $info[] = ['worksheetName' => (string) $name, ...$sheet];
            }

            return $info;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->reject('Il file Excel non può essere letto in sicurezza. Verifica il documento e riprova.');
        } finally {
            if ($opened) {
                $zip->close();
            }
        }
    }

    /** @return array<string, array{type: string, path: string}> */
    private function relationships(ZipArchive $zip, string $entry, string $source): array
    {
        $relations = [];
        $this->readXml($zip, $entry, function (XMLReader $xml) use (&$relations, $source) {
            if ($xml->nodeType !== XMLReader::ELEMENT || $xml->localName !== 'Relationship') {
                return;
            }
            $type = $xml->getAttribute('Type') ?? '';
            // External hyperlinks are legitimate, but never worksheet/workbook sources.
            if (! preg_match('~/(officeDocument|worksheet|chartsheet)$~', $type)) {
                return;
            }
            if ($xml->getAttribute('TargetMode') === 'External') {
                $this->reject('Il file Excel contiene riferimenti esterni non importabili.');
            }
            $target = $xml->getAttribute('Target') ?? '';
            if ($target === '' || strpbrk($target, ':\\?#') !== false) {
                $this->reject('Il file Excel contiene un percorso di foglio non valido.');
            }
            $path = str_starts_with($target, '/') ? $target : ($source === '' ? '' : dirname($source).'/').$target;
            $parts = [];
            foreach (explode('/', $path) as $part) {
                if ($part === '..') {
                    if (array_pop($parts) === null) {
                        $this->reject('Il file Excel contiene un percorso non valido.');
                    }
                } elseif ($part !== '' && $part !== '.') {
                    $parts[] = $part;
                }
            }
            $relations[$xml->getAttribute('Id')] = ['type' => $type, 'path' => implode('/', $parts)];
        });

        return $relations;
    }

    /** @return array<string, int|string> */
    private function worksheetInfo(ZipArchive $zip, string $entry): array
    {
        $row = $lastRow = $lastColumn = 0;
        $sheetDataDepth = $cellDepth = null;
        $validWorksheet = false;
        $hasSheetData = false;
        $this->readXml($zip, $entry, function (XMLReader $xml) use (&$row, &$lastRow, &$lastColumn, &$sheetDataDepth, &$cellDepth, &$validWorksheet, &$hasSheetData) {
            if (! in_array($xml->namespaceURI, self::MAIN_NAMESPACES, true)) {
                return;
            }
            if ($xml->nodeType === XMLReader::ELEMENT && $xml->localName === 'worksheet' && $xml->depth === 0) {
                $validWorksheet = true;
            }
            if ($xml->localName === 'sheetData') {
                $hasSheetData = true;
                $sheetDataDepth = $xml->nodeType === XMLReader::ELEMENT && ! $xml->isEmptyElement ? $xml->depth : null;
            }
            if ($sheetDataDepth === null) {
                return;
            }
            if ($xml->nodeType === XMLReader::ELEMENT && $xml->localName === 'row' && $xml->depth === $sheetDataDepth + 1) {
                $index = $xml->getAttribute('r') ?? '';
                if (! ctype_digit($index) || (int) $index <= $row || (int) $index > 1048576) {
                    $this->reject('Il file Excel contiene coordinate di riga non valide.');
                }
                $row = (int) $index;
                $cellDepth = null;
            }
            if ($xml->localName === 'c') {
                $cellDepth = null;
                if ($xml->nodeType === XMLReader::ELEMENT && $xml->depth === $sheetDataDepth + 2) {
                    $coordinate = $xml->getAttribute('r') ?? '';
                    if (! preg_match('/^([A-Z]{1,3})([1-9][0-9]{0,6})$/', $coordinate, $matches)
                        || (int) $matches[2] !== $row || Coordinate::columnIndexFromString($matches[1]) > 16384) {
                        $this->reject('Il file Excel contiene coordinate di cella non valide.');
                    }
                    $lastColumn = max($lastColumn, Coordinate::columnIndexFromString($matches[1]));
                    $cellDepth = $xml->isEmptyElement ? null : $xml->depth;
                }
            }
            if ($cellDepth !== null && $xml->nodeType === XMLReader::ELEMENT && $xml->depth > $cellDepth
                && ($xml->localName === 'f' || (in_array($xml->localName, ['v', 't'], true) && $xml->readString() !== ''))) {
                // Shared-string indexes (including "0"), zero, false, errors and
                // formulas are conservatively data. Never discard invalid rows.
                $lastRow = $row;
            }
        });
        if (! $validWorksheet || ! $hasSheetData) {
            $this->reject('Il file Excel contiene un foglio non valido.');
        }

        return [
            'totalRows' => $lastRow,
            'totalColumns' => $lastColumn,
            'lastColumnIndex' => max(0, $lastColumn - 1),
            'lastColumnLetter' => Coordinate::stringFromColumnIndex(max(1, $lastColumn)),
        ];
    }

    private function readXml(ZipArchive $zip, string $entry, Closure $visit): void
    {
        $stat = $zip->statName($entry);
        $limit = (int) config('imports.max_xml_bytes');
        if ($stat === false || $limit < 1 || $stat['size'] > $limit) {
            $this->reject('Il file Excel contiene un foglio mancante o troppo grande.');
        }
        $source = $zip->getStream($entry);
        $temporary = tmpfile();
        $xml = new XMLReader;
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            if ($source === false || $temporary === false
                || stream_copy_to_stream($source, $temporary, $limit + 1) !== $stat['size']) {
                $this->reject('Non è possibile leggere il file Excel.');
            }
            if (! $xml->open(stream_get_meta_data($temporary)['uri'], null, LIBXML_NONET | LIBXML_COMPACT)) {
                $this->reject('Il file Excel contiene XML non valido.');
            }
            while ($xml->read()) {
                if ($xml->nodeType === XMLReader::DOC_TYPE) {
                    $this->reject('Il file Excel contiene dichiarazioni XML non supportate.');
                }
                $visit($xml);
            }
            if (libxml_get_errors() !== []) {
                $this->reject('Il file Excel contiene XML non valido.');
            }
        } finally {
            $xml->close();
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($temporary)) {
                fclose($temporary);
            }
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['importFile' => $message]);
    }
}
