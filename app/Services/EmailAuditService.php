<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmailAuditService
{
    public function record(array $data): void
    {
        if (!Schema::hasTable('communication_email_logs')) return;

        DB::table('communication_email_logs')->insert([
            'channel' => $data['channel'] ?? 'smtp',
            'recipient' => $data['recipient'] ?? null,
            'subject' => $data['subject'] ?? null,
            'template_key' => $data['template_key'] ?? null,
            'status' => $data['status'] ?? 'queued',
            'provider' => $data['provider'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'triggered_by' => $data['triggered_by'] ?? session('admin_nome', 'Sistema'),
            'related_type' => $data['related_type'] ?? null,
            'related_id' => $data['related_id'] ?? null,
            'sent_at' => ($data['status'] ?? null) === 'sent' ? now() : null,
            'failed_at' => ($data['status'] ?? null) === 'failed' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
