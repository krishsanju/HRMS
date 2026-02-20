<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIDocService;
use App\Services\ProjectScannerService;
use App\Services\JiraService;

class GenerateJiraTickets extends Command
{
    protected $signature = 'jira:generate';
    protected $description = 'Analyze HRMS code and create Jira tickets';

    public function handle(
        AIDocService $ai,
        ProjectScannerService $scanner,
        JiraService $jira
    )
    {
        $this->info("Scanning project for Jira analysis...");

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

        // ========================================
        // NEW PROMPT FOR JIRA (SEPARATE FROM DOCS)
        // ========================================

        $prompt = "

            You are a Senior AI Engineering Agent responsible for generating
            a COMPLETE Jira backlog from a Laravel HRMS project.

            VERY IMPORTANT:

            You must create TWO TYPES of tickets:

            1) IMPLEMENTED FEATURES
            - Detect features already built in the code.
            - Generate Story tickets.
            - Status MUST be \"resolved\".

            Examples:
            Employee CRUD API
            Department Management
            Attendance Tracking
            Leave Management

            2) NEW FEATURES
            - Detect features which need to be built.
            - Generate Story tickets.
            - Status MUST be \"new\".

            3) ENGINEERING WORK ITEMS
            - Security issues
            - Missing validation
            - Technical debt
            - Architecture improvements
            - Database problems

            Status rules:

            resolved → feature already exists in code
            new → missing feature or improvement
            in progress → partially implemented logic

            Return ONLY valid JSON.

            JSON FORMAT:

            {
            \"tickets\":[
            {
            \"title\":\"\",
            \"type\":\"Bug | Story | Task | Improvement\",
            \"priority\":\"Low | Medium | High | Critical\",
            \"status\":\"new | in progress | resolved\",
            \"description\":\"HTML\",
            \"acceptance_criteria\":[\"\"],
            \"labels\":[\"ai-generated\",\"hrms\"]
            }
            ]
            }

            Analyze this source code:

            ".$source;



        $this->info("Calling Gemini for Jira tickets...");

        $result = $ai->generate($prompt);
        

        foreach(($result['tickets'] ?? []) as $ticket){
            if($jira->issueExists($ticket['title'])){
                $this->warn("Skipping existing ticket: ".$ticket['title']);
                continue;
            }

            $issue = $jira->createIssue($ticket);

            info("Jira create response: ".json_encode($issue));


            // OPTIONAL: Move status automatically
            if(isset($ticket['status']) && isset($issue['key'])){

                // Example transition ID (depends on workflow)
                $jira->transitionFromAIStatus($issue['key'], $ticket['status']);
            }
        }


        $this->info("AI Jira Tickets Created!");
    }
}
