<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SlipOcrService
{
    public function verify(string $imagePath): array
    {
        if (config('ocr.provider') === 'google' && !empty(config('ocr.api_key')))
            return $this->googleVision($imagePath);
        return $this->regexFallback($imagePath);
    }
    private function googleVision(string $path): array
    {
        try {
            $client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient(['keyFilePath' => config('ocr.google_credentials')]);
            $image = file_get_contents($path);
            $resp = $client->textDetection($image);
            $texts = $resp->getTextAnnotations();
            $fullText = $texts->isEmpty() ? '' : $texts[0]->getDescription();
            return $this->parseText($fullText);
        } catch (\Exception $e) { Log::error('Google OCR: '.$e->getMessage()); return ['verified' => false, 'error' => 'OCR service unavailable']; }
    }
    private function regexFallback(string $path): array
    {
        $text = strtoupper(file_get_contents($path));
        $amount = 0; $ref = null;
        preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $text, $m) && $amount = (float) str_replace(',', '', $m[0]);
        preg_match('/(?:REF|TRANS|SLIP)\s*[#:]?\s*([A-Z0-9]{6,20})/i', $text, $m) && $ref = $m[1];
        return ['verified' => $amount > 0, 'amount' => $amount, 'ref' => $ref, 'method' => 'regex'];
    }
    private function parseText(string $text): array
    {
        $amount = 0; $ref = null; $bank = null;
        preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $text, $m) && $amount = (float) str_replace(',', '', $m[0]);
        preg_match('/(?:REF|TRANS|SLIP)\s*[#:]?\s*([A-Z0-9]{6,20})/i', $text, $m) && $ref = $m[1];
        if (preg_match('/(SCB|KBANK|BBL|KTB|TTB|GSB|BAAC|KRUNGSRI)/i', $text, $m)) $bank = $m[1];
        return ['verified' => $amount > 0, 'amount' => $amount, 'ref' => $ref, 'bank' => $bank, 'method' => 'google'];
    }
}
