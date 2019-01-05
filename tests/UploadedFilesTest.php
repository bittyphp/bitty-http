<?php

namespace Bitty\Tests\Http;

use Bitty\Http\UploadedFile;
use Bitty\Http\UploadedFiles;
use PHPUnit\Framework\TestCase;

class UploadedFilesTest extends TestCase
{
    /**
     * @param array $files
     * @param array $expectedData
     *
     * @dataProvider sampleFileData
     */
    public function testLoadingFiles(array $files, array $expectedData): void
    {
        $expected = $this->getExpectedData($expectedData);

        $fixture = new UploadedFiles();
        $actual  = $fixture->collapseFileTree($files);

        self::assertEquals($expected, $actual);
    }

    public function sampleFileData(): array
    {
        $fieldA = uniqid('field');
        $fieldB = uniqid('field');
        $fieldC = uniqid('field');
        $pathA  = uniqid('path');
        $pathB  = uniqid('path');
        $pathC  = uniqid('path');
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $nameC  = uniqid('name');
        $typeA  = uniqid('type');
        $typeB  = uniqid('type');
        $typeC  = uniqid('type');
        $sizeA  = rand();
        $sizeB  = rand();
        $sizeC  = rand();
        $errorA = rand();
        $errorB = rand();
        $errorC = rand();

        return [
            'single file, one field' => [
                'files' => [
                    $fieldA => [
                        'tmp_name' => $pathA,
                        'name' => $nameA,
                        'type' => $typeA,
                        'size' => $sizeA,
                        'error' => $errorA,
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        'tmp_name' => $pathA,
                        'name' => $nameA,
                        'type' => $typeA,
                        'size' => $sizeA,
                        'error' => $errorA,
                    ],
                ],
            ],
            'multiple files, one field' => [
                'files' => [
                    $fieldA => [
                        'tmp_name' => [$pathA, $pathB],
                        'name' => [$nameA, $nameB],
                        'type' => [$typeA, $typeB],
                        'size' => [$sizeA, $sizeB],
                        'error' => [$errorA, $errorB],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        [
                            'tmp_name' => $pathA,
                            'name' => $nameA,
                            'type' => $typeA,
                            'size' => $sizeA,
                            'error' => $errorA,
                        ],
                        [
                            'tmp_name' => $pathB,
                            'name' => $nameB,
                            'type' => $typeB,
                            'size' => $sizeB,
                            'error' => $errorB,
                        ],
                    ],
                ],
            ],
            'single file, multiple fields' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                        ],
                    ],
                ],
            ],
            'multiple files, multiple fields' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => [$pathA, $pathB],
                                'name' => [$nameA, $nameB],
                                'type' => [$typeA, $typeB],
                                'size' => [$sizeA, $sizeB],
                                'error' => [$errorA, $errorB],
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                [
                                    'tmp_name' => $pathA,
                                    'name' => $nameA,
                                    'type' => $typeA,
                                    'size' => $sizeA,
                                    'error' => $errorA,
                                ],
                                [
                                    'tmp_name' => $pathB,
                                    'name' => $nameB,
                                    'type' => $typeB,
                                    'size' => $sizeB,
                                    'error' => $errorB,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'mixed' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            'tmp_name' => [$pathA, $pathB],
                            'name' => [$nameA, $nameB],
                            'type' => [$typeA, $typeB],
                            'size' => [$sizeA, $sizeB],
                            'error' => [$errorA, $errorB],
                        ],
                    ],
                    $fieldC => [
                        'tmp_name' => $pathC,
                        'name' => $nameC,
                        'type' => $typeC,
                        'size' => $sizeC,
                        'error' => $errorC,
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                            [
                                'tmp_name' => $pathB,
                                'name' => $nameB,
                                'type' => $typeB,
                                'size' => $sizeB,
                                'error' => $errorB,
                            ],
                        ],
                    ],
                    $fieldC => [
                        'tmp_name' => $pathC,
                        'name' => $nameC,
                        'type' => $typeC,
                        'size' => $sizeC,
                        'error' => $errorC,
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds a similar array structure as to what is expected.
     *
     * @param mixed[] $files
     *
     * @return array Nested array of UploadedFile|UploadedFile[]
     */
    protected function getExpectedData(array $files): array
    {
        $expected = [];

        foreach ($files as $field => $file) {
            if (!array_key_exists('error', $file)) {
                $expected[$field] = $this->getExpectedData($file);
            } else {
                $expected[$field] = new UploadedFile(
                    $file['tmp_name'],
                    $file['name'],
                    $file['type'],
                    $file['size'],
                    $file['error']
                );
            }
        }

        return $expected;
    }
}
