<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ProjectScannerService
{
    public function controllers()
    {
        return File::files(app_path('Http/Controllers/API'));
    }

    public function models()
    {
        return File::files(app_path('Models'));
    }

    public function migrations()
    {
        return File::files(database_path('migrations'));
    }

    public function getContent($file)
    {
        return File::get($file->getRealPath());
    }
}
