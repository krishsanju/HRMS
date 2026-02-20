<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;


class AIDocService
{
   public function generate(string $prompt): array
    {
  
        $response = Http::timeout(180)      // allow 3 minutes
            ->connectTimeout(30)
            ->retry(2, 3000)                // retry twice if slow
            ->post(
            "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . env('GEMINI_API_KEY'),
            [
                "contents" => [
                    [
                        "parts" => [
                           ["text" => $prompt]

                        ]
                    ]
                ]
            ]
        ); 

 
        $text = data_get(
        $response->json(),
            'candidates.0.content.parts.0.text',
            '{}'                                                            
        );

         // Remove markdown wrappers if Gemini adds them
        $text = preg_replace('/```json|```/', '', $text);

        // Remove invalid control characters (VERY IMPORTANT)
        $text = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);

        // Trim spaces
        $text = trim($text);

        // Decode safely
        $decoded = json_decode($text, true);

        if(json_last_error() !== JSON_ERROR_NONE){
            info("JSON Decode Error: ".json_last_error_msg());
            info("Cleaned AI Text: ".$text);
            return [];
        }

        return $decoded;
    }

}
