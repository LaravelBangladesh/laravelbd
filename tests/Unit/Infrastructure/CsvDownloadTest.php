<?php

use App\Infrastructure\Csv\CsvDownload;

test('a cell a spreadsheet would run as a formula is kept as text', function (?string $value, string $expected) {
    expect(CsvDownload::cell($value))->toBe($expected);
})->with([
    'equals' => ['=SUM(A1:A2)', "'=SUM(A1:A2)"],
    'phone number' => ['+8801712345678', '+8801712345678'],
    'plus formula' => ['+1+cmd', "'+1+cmd"],
    'plus function' => ['+SUM(1)', "'+SUM(1)"],
    'negative number' => ['-1', "'-1"],
    'minus' => ['-2+3', "'-2+3"],
    'at' => ['@cmd', "'@cmd"],
    'tab' => ["\t=1", "'\t=1"],
    'carriage return' => ["\r=1", "'\r=1"],
    'plain text' => ['Ada Lovelace', 'Ada Lovelace'],
    'formula later in the cell' => ['Ada =1', 'Ada =1'],
    'empty' => ['', ''],
    'null' => [null, ''],
]);
