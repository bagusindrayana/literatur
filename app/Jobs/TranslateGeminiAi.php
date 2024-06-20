<?php

namespace App\Jobs;

use App\Models\TranslatePage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

class TranslateGeminiAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $apiKey;
    public $text;
    public $prePrompt;
    public $dataId;

    /**
     * Create a new job instance.
     */
    public function __construct($apiKey, $text, $prePrompt,$dataId)
    {
        $this->apiKey = $apiKey;
        $this->text = $text;
        $this->prePrompt = $prePrompt;
        $this->dataId = $dataId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client($this->apiKey);
        $response = $client->geminiPro()->generateContent(
            new TextPart($this->prePrompt."\n".$this->text),
        );
        TranslatePage::find($this->dataId)->update([
            'translated_text' => $response->text(),
        ]);
    }
}
