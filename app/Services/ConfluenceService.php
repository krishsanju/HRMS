<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ConfluenceService
{
    protected function client()
    {
        return Http::withBasicAuth(
            env('CONF_EMAIL'),
            env('CONF_TOKEN')
        );
    }

    public function upsertPage($title, $html, $parentId = null)
    {
        $existing = $this->findPageByTitle($title);

        //IF PAGE EXISTS → UPDATE
        if ($existing) {
            return $this->updatePage(
                $existing['id'],
                $title,
                $html,
                $existing['version']['number']
            );
        }

        //ELSE → CREATE NEW PAGE
        $payload = [
            "type" => "page",
            "title" => $title,
            "space" => ["key" => env('CONF_SPACE')],
            "body" => [
                "storage" => [
                    "value" => $html,
                    "representation" => "storage"
                ]
            ]
        ];

        if ($parentId) {
            $payload['ancestors'] = [["id" => $parentId]];
        }

        return $this->client()
            ->post(env('CONF_BASE_URL').'/rest/api/content', $payload)
            ->json();
    }


    public function updatePage($pageId, $title, $html, $version)
    {
        return $this->client()->put(
            env('CONF_BASE_URL')."/rest/api/content/{$pageId}",
            [
                "id" => $pageId,
                "type" => "page",
                "title" => $title,
                "version" => [
                    "number" => $version + 1
                ],
                "body" => [
                    "storage" => [
                        "value" => $html,
                        "representation" => "storage"
                    ]
                ]
            ]
        )->json();
    }


    public function findPageByTitle($title)
    {
        $response = $this->client()->get(
            env('CONF_BASE_URL').'/rest/api/content',
            [
                'title' => $title,
                'spaceKey' => env('CONF_SPACE'),
                'expand' => 'version'
            ]
        );

        $data = $response->json();

        return $data['results'][0] ?? null;
    }

}
