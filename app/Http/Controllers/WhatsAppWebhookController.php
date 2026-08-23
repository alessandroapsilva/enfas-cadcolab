<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsapp) {}

    public function verify(Request $request)
    {
        $cfg = $this->whatsapp->configuration();
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && !empty($cfg['verify_token']) && hash_equals((string) $cfg['verify_token'], (string) $token)) {
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request)
    {
        $raw = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256');
        if (!$this->whatsapp->validateSignature($raw, $signature)) {
            return response()->json(['ok' => false], 401);
        }

        $this->whatsapp->processWebhook($request->json()->all());
        return response()->json(['ok' => true]);
    }
}
