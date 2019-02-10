<?php

namespace Bitty\Http;

use Bitty\Http\UploadedFile;

class UploadedFiles
{
    /**
     * Collapses a file tree into a usable structure.
     *
     * A single file entry might be one file, an array of files, or an array
     * of infinite arrays of files.
     *
     * The input here is expected to match that of $_FILES.
     *
     * @see http://www.php-fig.org/psr/psr-7/#16-uploaded-files
     *
     * @param array $files
     *
     * @return array
     */
    public function collapseFileTree(array $files): array
    {
        $tree = [];

        foreach ($files as $field => $file) {
            // array of array of files
            if (!isset($file['error'])) {
                if (is_array($file)) {
                    $tree[$field] = $this->collapseFileTree($file);
                }

                continue;
            }

            // single file
            if (!is_array($file['error'])) {
                $tree[$field] = new UploadedFile(
                    $file['tmp_name'] ?? null,
                    $file['name'] ?? null,
                    $file['type'] ?? null,
                    $file['size'] ?? null,
                    $file['error'] ?? null
                );

                continue;
            }

            // array of files
            $list = [];
            foreach ($file['error'] as $index => $junk) {
                $list[] = new UploadedFile(
                    $file['tmp_name'][$index] ?? null,
                    $file['name'][$index] ?? null,
                    $file['type'][$index] ?? null,
                    $file['size'][$index] ?? null,
                    $file['error'][$index] ?? null
                );
            }

            $tree[$field] = $list;
        }

        return $tree;
    }
}
