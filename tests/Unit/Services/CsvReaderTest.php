<?php

use App\Services\CsvReader;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

it('throws an exception if file does not exist', function () {
    $csvReader = new CsvReader;

    // Expect an exception when trying to read a non-existent file.
    expect(fn () => $csvReader->readCsvFile('invalid_file.csv'))
        ->toThrow(Exception::class, 'The file at path invalid_file.csv does not exist.');
});

it('processes CSV with duplicate headers correctly', function () {
    // Prepare CSV content that includes duplicate headers.
    $csvContent = <<<'CSV'
Name,Age,Name
John,30,Doe
Alice,25,Wonderland
CSV;

    $filePath = 'test.csv';
    Storage::put($filePath, $csvContent);

    $csvReader = new CsvReader;
    $result = $csvReader->readCsvFile($filePath);

    // Expect the duplicate header "Name" to be de-duplicated: first occurrence is "Name", second is "Name__1"
    $expected = [
        [
            'Name' => 'John',
            'Age' => '30',
            'Name__1' => 'Doe',
        ],
        [
            'Name' => 'Alice',
            'Age' => '25',
            'Name__1' => 'Wonderland',
        ],
    ];

    expect($result)->toEqual($expected);
});

it('processes CSV with unique headers correctly', function () {
    // Prepare CSV content with unique headers.
    $csvContent = <<<'CSV'
A,B,C
1,2,3
4,5,6
CSV;

    $filePath = 'unique.csv';
    Storage::put($filePath, $csvContent);

    $csvReader = new CsvReader;
    $result = $csvReader->readCsvFile($filePath);

    $expected = [
        [
            'A' => '1',
            'B' => '2',
            'C' => '3',
        ],
        [
            'A' => '4',
            'B' => '5',
            'C' => '6',
        ],
    ];

    expect($result)->toEqual($expected);
});
