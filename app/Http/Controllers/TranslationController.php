<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{

    public function showForm()
    {
        return view('translate');
    }

    public function translate(Request $request)
{
    $request->validate([
        'phrase' => 'required|string',
        'source-language' => 'required|string',
        'language' => 'required|string',
    ]);

    $phrase = $request->input('phrase');
    $sourceLanguage = $request->input('source-language');
    $targetLanguage = $request->input('language');

    // Log inputs for easier debugging
    Log::info("Phrase: $phrase");
    Log::info("Source Language: $sourceLanguage");
    Log::info("Target Language: $targetLanguage");

    // Call the translation API
    $translatedPhrase = $this->callTranslationApi($phrase, $sourceLanguage, $targetLanguage);

    return response()->json(['translatedPhrase' => $translatedPhrase]);
}


    private function callTranslationApi($phrase, $sourceLanguage, $targetLanguage)
    {
        $client = new Client();
        $url = 'https://api.mymemory.translated.net/get';

        try {
            $response = $client->get($url, [
                'query' => [
                    'q' => $phrase,
                    'langpair' => $sourceLanguage . '|' . $targetLanguage,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Check for errors in the response
            if (isset($data['responseStatus']) && $data['responseStatus'] !== 200) {
                return 'Translation failed: ' . ($data['responseDetails'] ?? 'Unknown error');
            }

            return $data['responseData']['translatedText'] ?? 'Translation failed.';
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Translation API error: ' . $e->getMessage());
            return 'Error contacting translation service.';
        }
    }
}
