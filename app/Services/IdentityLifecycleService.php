<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IdentityLifecycleService
{
    public function __construct(private readonly IdentityPolicyService $policies) {}

    public function evaluate(array $identity, string $eventType = 'sync'): array
    {
        $correlationId = (string) Str::uuid();
        $policy = $this->policies->resolve($identity);

        if (Schema::hasTable('identity_provisioning_events')) {
            DB::table('identity_provisioning_events')->insert([
                'correlation_id' => $correlationId,
                'username' => $identity['username'] ?? null,
                'event_type' => $eventType,
                'target' => 'policy-engine',
                'status' => 'completed',
                'payload' => json_encode(['identity' => $this->safeIdentity($identity), 'policy' => $policy], JSON_UNESCAPED_UNICODE),
                'message' => $policy['matched'] ? 'Políticas aplicáveis resolvidas.' : 'Nenhuma política específica; perfil padrão mantido.',
                'started_at' => now(),
                'finished_at' => now(),
                'created_by' => session('admin_nome', 'Sistema'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ['correlation_id' => $correlationId, 'policy' => $policy];
    }

    public function record(string $username, string $eventType, string $target, string $status, ?string $message = null, array $payload = []): string
    {
        $correlationId = (string) Str::uuid();
        if (Schema::hasTable('identity_provisioning_events')) {
            DB::table('identity_provisioning_events')->insert([
                'correlation_id' => $correlationId,
                'username' => $username,
                'event_type' => $eventType,
                'target' => $target,
                'status' => $status,
                'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
                'message' => $message,
                'started_at' => now(),
                'finished_at' => in_array($status, ['completed', 'failed', 'skipped'], true) ? now() : null,
                'created_by' => session('admin_nome', 'Sistema'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $correlationId;
    }

    private function safeIdentity(array $identity): array
    {
        return collect($identity)->except(['password', 'bind_password', 'senha'])->all();
    }
}
