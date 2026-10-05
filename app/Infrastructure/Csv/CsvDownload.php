<?php

namespace App\Infrastructure\Csv;

use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams rows straight to the client, so an export of any size keeps memory
 * flat as long as the rows come from a lazy source.
 */
final class CsvDownload
{
    /**
     * Spreadsheet apps run a cell that starts with one of these as a formula.
     */
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * @param  list<string>  $header
     * @param  iterable<list<string|null>>  $rows
     */
    public static function make(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows): void {
            $output = new SplFileObject('php://output', 'w');
            // The byte order mark tells Excel the file is UTF-8, so Bangla reads right.
            $output->fwrite("\xEF\xBB\xBF");
            $output->fputcsv(array_map(self::cell(...), $header), escape: '');

            foreach ($rows as $row) {
                $output->fputcsv(array_map(self::cell(...), $row), escape: '');
            }
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prefix a quote so the cell reads as text, never as a formula. A bare
     * phone number such as +8801712345678 cannot run, so it stays as is.
     */
    public static function cell(?string $value): string
    {
        $value = (string) $value;

        return $value !== ''
            && in_array($value[0], self::FORMULA_PREFIXES, true)
            && preg_match('/^\+\d+$/', $value) !== 1
            ? "'".$value
            : $value;
    }
}
