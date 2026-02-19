<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JiraService
{
    protected function client()
    {
        return Http::withBasicAuth(
            config('services.jira.email'),
            config('services.jira.token')
        )->baseUrl(config('services.jira.url'));
    }

    // ===================================================
    // CREATE ISSUE WITH TYPE + AC + PRIORITY
    // ===================================================
    public function createIssue(array $data)
    {
        $response = $this->client()->post('/rest/api/3/issue', [
            "fields" => [
                "project" => [
                    "key" => config('services.jira.project')
                ],
                "summary" => $data['title'],

                // AC + Description
                "description" => $this->formatADF(
                    $data['description'] ?? '',
                    $data['acceptance_criteria'] ?? []
                ),

                "issuetype" => [
                    "name" => $this->normalizeIssueType($data['type'] ?? 'Task')
                ],

                "priority" => [
                    "name" => $this->normalizePriority($data['priority'] ?? "Medium")
                ],

                "labels" => $data['labels'] ?? ['AI-GENERATED']
            ]
        ])->json();

        return $response;
    }

    // ===================================================
    // CHECK IF ISSUE ALREADY EXISTS BY SUMMARY
    // ===================================================
    public function issueExists($summary)
    {
        $jql = 'project="'.config('services.jira.project').'" AND summary ~ "'.addslashes($summary).'"';

        $response = $this->client()->get('/rest/api/3/search', [
            'jql' => $jql,
            'maxResults' => 1
        ])->json();

        return ($response['total'] ?? 0) > 0;
    }


    // ===================================================
    // TRANSITION ISSUE STATUS
    // ===================================================
    public function transitionIssue($issueKey, $transitionId)
    {
        return $this->client()->post("/rest/api/3/issue/{$issueKey}/transitions", [
            "transition" => [
                "id" => $transitionId
            ]
        ])->json();
    }

    public function transitionFromAIStatus($issueKey, $status)
    {
        $map = [
            'new' => null,          // stay in To Do
            'in progress' => '21',  // example ID
            'resolved' => '31',     // example Done ID
        ];

        if(!isset($map[$status]) || !$map[$status]){
            return;
        }

        return $this->transitionIssue($issueKey, $map[$status]);
    }


    // ===================================================
    // FORMAT DESCRIPTION + ACCEPTANCE CRITERIA
    // ===================================================
    protected function formatADF($description, $acs = [])
    {
        $content = [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => strip_tags($description)
                    ]
                ]
            ]
        ];

        if(!empty($acs)){

            $content[] = [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "Acceptance Criteria:"
                    ]
                ]
            ];

            foreach($acs as $ac){
                $content[] = [
                    "type" => "paragraph",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => "- ".strip_tags($ac)
                        ]
                    ]
                ];
            }
        }

        return [
            "type" => "doc",
            "version" => 1,
            "content" => $content
        ];
    }

    protected function normalizeIssueType($type)
    {
        $map = [
            'Bug' => 'Bug',
            'Story' => 'Story',
            'Task' => 'Task',
            'Improvement' => 'Task',
            'Technical Debt' => 'Task',
        ];

        return $map[$type] ?? 'Task';
    }

    protected function normalizePriority($priority)
    {
        $map = [
            'Critical' => 'Highest',
            'High' => 'High',
            'Medium' => 'Medium',
            'Low' => 'Low'
        ];

        return $map[$priority] ?? 'Medium';
    }


}
