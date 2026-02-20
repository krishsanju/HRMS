<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\AIDocService;
use App\Services\ConfluenceService;
use App\Services\ProjectScannerService;

class GenerateConfluenceDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'confluence:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Confluence documentation for HRMS API';

    /**
     * Execute the console command.
     */
   public function handle(
    AIDocService $ai,
    ConfluenceService $confluence,
    ProjectScannerService $scanner
){
    $this->info("Scanning project...");

    // ===================================================
    // COLLECT ENTIRE PROJECT SOURCE
    // ===================================================
    $source = "";

    foreach($scanner->controllers() as $file){
        $source .= $scanner->getContent($file)."\n\n";
    }

    foreach($scanner->models() as $file){
        $source .= $scanner->getContent($file)."\n\n";
    }

    foreach($scanner->migrations() as $file){
        $source .= $scanner->getContent($file)."\n\n";
    }

    // ===================================================
    // SINGLE AI CALL
    // ===================================================
    $this->info("Calling AI once...");

    $docs = $ai->generate("You are an AI Engineering Documentation Agent.

                                Analyze this Laravel HRMS project and RETURN ONLY VALID JSON.

                                Format EXACTLY like this:

                                {
                                \"architecture\": \"<h1>Architecture Overview</h1>\",
                                \"modules\": [
                                    {\"title\":\"Employee\",\"html\":\"<h2>Employee Module</h2>\"},
                                    {\"title\":\"Department\",\"html\":\"<h2>Department Module</h2>\"},
                                    {\"title\":\"Attendance\",\"html\":\"<h2>Attendance Module</h2>\"},
                                    {\"title\":\"Leave\",\"html\":\"<h2>Leave Module</h2>\"}
                                ],
                                \"database\": \"<h1>Database Design</h1>\"
                                }

                                Return ONLY JSON. No explanations. No markdown.

                                Laravel source code:

                                ".$source);

    // ===================================================
    // CREATE ROOT PAGE
    // ===================================================
    $root = $confluence->upsertPage(
        "HRMS Enterprise Docs",
        "<h1>HRMS Enterprise Documentation</h1>"
    );

    $rootId = $root['id'] ?? null;

    if(!$rootId){
        $this->error("Root page failed");
        return;
    }

    // ===================================================
    // ARCHITECTURE PAGE
    // ===================================================
    $confluence->upsertPage(
        "Architecture Overview",
        $docs['architecture'] ?? "<p>No data</p>",
        $rootId
    );

    // ===================================================
    // MODULE PAGES
    // ===================================================
    foreach(($docs['modules'] ?? []) as $module){

        $confluence->upsertPage(
            $module['title'],
            $module['html'],
            $rootId
        );
    }

    // ===================================================
    // DATABASE PAGE
    // ===================================================
    $confluence->upsertPage(
        "Database Design",
        $docs['database'] ?? "<p>No DB docs</p>",
        $rootId
    );

    $this->info("Single-AI-Call Enterprise Docs Generated!");
}

}
