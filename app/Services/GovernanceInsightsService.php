<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GovernanceInsightsService
{
    public static function generate(): array
    {
        $items = [];
        $push = function ($severity, $title, $message, $action, $module) use (&$items) {
            $items[] = compact('severity', 'title', 'message', 'action', 'module');
        };

        if (Schema::hasTable('logs_erros') && Schema::hasColumn('logs_erros', 'data_hora')) {
            $recent = DB::table('logs_erros')->where('data_hora', '>=', now()->subDays(7))->count();
            if ($recent >= 10) {
                $push('high', 'Volume elevado de falhas', "Foram registrados {$recent} erros nos últimos 7 dias.", 'Priorizar os módulos com maior recorrência e revisar credenciais/permissões.', 'observabilidade');
            } elseif ($recent > 0) {
                $push('medium', 'Erros recentes detectados', "Há {$recent} falhas recentes registradas.", 'Revisar a Central de Erros antes do próximo ciclo de provisionamento.', 'observabilidade');
            }
        }

        if (Schema::hasTable('communication_email_logs') && Schema::hasColumn('communication_email_logs', 'status')) {
            $query = DB::table('communication_email_logs')->where('status', 'failed');
            if (Schema::hasColumn('communication_email_logs', 'created_at')) {
                $query->where('created_at', '>=', now()->subDays(30));
            }
            $failed = $query->count();
            if ($failed > 0) {
                $push($failed >= 5 ? 'high' : 'medium', 'Falhas de e-mail', "Existem {$failed} e-mails com falha no período analisado.", 'Validar SMTP, destinatários e reprocessar as comunicações pendentes.', 'comunicacoes');
            }
        }

        if (Schema::hasTable('identity_directory_users')) {
            $syncColumn = collect(['last_synced_at', 'synced_at', 'updated_at'])
                ->first(fn ($column) => Schema::hasColumn('identity_directory_users', $column));

            if ($syncColumn) {
                $stale = DB::table('identity_directory_users')
                    ->where(function ($query) use ($syncColumn) {
                        $query->whereNull($syncColumn)->orWhere($syncColumn, '<', now()->subDays(2));
                    })
                    ->count();

                if ($stale > 0) {
                    $push('medium', 'Diretório desatualizado', "{$stale} identidades estão sem sincronização recente.", 'Executar sincronização LDAP e revisar objetos que continuam divergentes.', 'identidade');
                }
            }
        }

        if (Schema::hasTable('identity_directory_settings') && Schema::hasColumn('identity_directory_settings', 'enabled')) {
            $enabled = (bool) DB::table('identity_directory_settings')->value('enabled');
            if (!$enabled) {
                $push('low', 'LDAP ainda não é a autoridade principal', 'A autenticação de diretório está desativada.', 'Após validar usuários e grupos, ativar LDAP e manter fallback local apenas para contingência.', 'identidade');
            }
        }

        if (Schema::hasTable('whatsapp_messages') && Schema::hasColumn('whatsapp_messages', 'status')) {
            $query = DB::table('whatsapp_messages')->whereIn('status', ['failed', 'error']);
            if (Schema::hasColumn('whatsapp_messages', 'created_at')) {
                $query->where('created_at', '>=', now()->subDays(30));
            }
            $failed = $query->count();
            if ($failed > 0) {
                $push('medium', 'Mensagens WhatsApp com falha', "{$failed} mensagens recentes não concluíram o envio.", 'Revisar templates, token Meta e números de destino.', 'whatsapp');
            }
        }

        if (!$items) {
            $push('ok', 'Ambiente saudável', 'Nenhum risco operacional relevante foi identificado pelas regras locais.', 'Continuar acompanhando integrações, auditoria e sincronizações.', 'governanca');
        }

        $weights = ['high' => 20, 'medium' => 10, 'low' => 4, 'ok' => 0];
        $score = max(0, 100 - array_sum(array_map(fn ($item) => $weights[$item['severity']] ?? 0, $items)));

        return [
            'score' => $score,
            'generated_at' => now()->toIso8601String(),
            'items' => $items,
        ];
    }
}
