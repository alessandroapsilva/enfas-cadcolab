<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class WhatsAppService
{
    public function configuration(): array
    {
        $cfg = Schema::hasTable('configuracoes')
            ? DB::table('configuracoes')->pluck('valor', 'chave')->toArray()
            : [];

        return [
            'token' => $cfg['wp_token'] ?? null,
            'phone_id' => $cfg['wp_phone_id'] ?? null,
            'business_id' => $cfg['wp_business_id'] ?? null,
            'verify_token' => $cfg['wp_verify_token'] ?? null,
            'app_secret' => $cfg['wp_app_secret'] ?? null,
            'graph_version' => $cfg['wp_graph_version'] ?? 'v23.0',
        ];
    }

    public function sendTemplate(string $phone, string $template, array $parameters = [], string $language = 'pt_BR'): array
    {
        $cfg = $this->configuration();
        if (empty($cfg['token']) || empty($cfg['phone_id'])) {
            throw new RuntimeException('WhatsApp Cloud API não configurada.');
        }

        $bodyParams = array_map(fn ($value) => ['type' => 'text', 'text' => (string) $value], $parameters);
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($phone),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language],
                'components' => $bodyParams ? [[
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ]] : [],
            ],
        ];

        $response = Http::withToken($cfg['token'])
            ->timeout(20)
            ->retry(2, 500)
            ->post("https://graph.facebook.com/{$cfg['graph_version']}/{$cfg['phone_id']}/messages", $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Falha ao enviar WhatsApp: '.$response->body());
        }

        $messageId = data_get($response->json(), 'messages.0.id');
        $conversation = $this->conversationFor($phone);
        if (Schema::hasTable('whatsapp_messages')) {
            DB::table('whatsapp_messages')->insert([
                'conversation_id' => $conversation,
                'meta_message_id' => $messageId,
                'direction' => 'out',
                'type' => 'template',
                'body' => $template,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'status' => 'sent',
                'message_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ['success' => true, 'message_id' => $messageId];
    }

    public function validateSignature(string $rawBody, ?string $signature): bool
    {
        $secret = $this->configuration()['app_secret'];
        if (!$secret) return false;
        if (!$signature || !str_starts_with($signature, 'sha256=')) return false;
        $expected = 'sha256='.hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }

    public function processWebhook(array $payload): void
    {
        $eventId = null;
        if (Schema::hasTable('whatsapp_webhook_events')) {
            $eventId = DB::table('whatsapp_webhook_events')->insertGetId([
                'event_key' => data_get($payload, 'entry.0.id'),
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'processed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            foreach ((array) data_get($payload, 'entry', []) as $entry) {
                foreach ((array) ($entry['changes'] ?? []) as $change) {
                    $value = $change['value'] ?? [];
                    $contacts = collect($value['contacts'] ?? [])->keyBy('wa_id');

                    foreach (($value['messages'] ?? []) as $message) {
                        $waId = $message['from'] ?? '';
                        $contact = $contacts->get($waId, []);
                        $conversationId = $this->conversationFor($waId, data_get($contact, 'profile.name'));
                        $type = $message['type'] ?? 'unknown';
                        $body = $this->messageBody($message);

                        DB::table('whatsapp_messages')->updateOrInsert(
                            ['meta_message_id' => $message['id'] ?? null],
                            [
                                'conversation_id' => $conversationId,
                                'direction' => 'in',
                                'type' => $type,
                                'body' => $body,
                                'payload' => json_encode($message, JSON_UNESCAPED_UNICODE),
                                'status' => 'received',
                                'message_at' => isset($message['timestamp']) ? date('Y-m-d H:i:s', (int) $message['timestamp']) : now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );

                        DB::table('whatsapp_conversations')->where('id', $conversationId)->update([
                            'last_message_at' => now(),
                            'unread_count' => DB::raw('unread_count + 1'),
                            'updated_at' => now(),
                        ]);
                    }

                    foreach (($value['statuses'] ?? []) as $status) {
                        if (!empty($status['id'])) {
                            DB::table('whatsapp_messages')->where('meta_message_id', $status['id'])->update([
                                'status' => $status['status'] ?? null,
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            if ($eventId) DB::table('whatsapp_webhook_events')->where('id', $eventId)->update(['processed' => true, 'updated_at' => now()]);
        } catch (\Throwable $e) {
            if ($eventId) DB::table('whatsapp_webhook_events')->where('id', $eventId)->update(['error' => $e->getMessage(), 'updated_at' => now()]);
            throw $e;
        }
    }

    private function conversationFor(string $phone, ?string $name = null): ?int
    {
        if (!Schema::hasTable('whatsapp_conversations')) return null;
        $waId = $this->normalizePhone($phone);
        $row = DB::table('whatsapp_conversations')->where('wa_id', $waId)->first();
        if ($row) {
            if ($name && !$row->contact_name) DB::table('whatsapp_conversations')->where('id', $row->id)->update(['contact_name' => $name, 'updated_at' => now()]);
            return $row->id;
        }
        return DB::table('whatsapp_conversations')->insertGetId([
            'wa_id' => $waId,
            'phone' => $waId,
            'contact_name' => $name,
            'status' => 'open',
            'unread_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function messageBody(array $message): ?string
    {
        return match ($message['type'] ?? '') {
            'text' => data_get($message, 'text.body'),
            'button' => data_get($message, 'button.text'),
            'interactive' => data_get($message, 'interactive.button_reply.title') ?? data_get($message, 'interactive.list_reply.title'),
            default => null,
        };
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }
}
