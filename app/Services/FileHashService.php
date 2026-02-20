<?php 

namespace App\Services;

use Illuminate\Support\Facades\File;

class FileHashService
{
    protected $cacheFile = 'storage/app/doc_hashes.json';

    public function calculateHashes(array $files): array
    {
        $hashes = [];

        foreach ($files as $file) {
            $hashes[$file->getFilename()] = md5_file($file->getRealPath());
        }

        return $hashes;
    }

    public function hasChanges(array $newHashes): bool
    {
        if (!File::exists($this->cacheFile)) {
            File::put($this->cacheFile, json_encode($newHashes));
            return true;
        }

        $old = json_decode(File::get($this->cacheFile), true);

        if ($old !== $newHashes) {
            File::put($this->cacheFile, json_encode($newHashes));
            return true;
        }

        return false;
    }
}
