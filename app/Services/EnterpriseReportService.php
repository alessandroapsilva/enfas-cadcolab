<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnterpriseReportService
{
    public static function summary(): array
    {
        return [
            'generated_at'=>now()->toIso8601String(),
            'errors_7d'=>self::countSince('logs_erros','data_hora',7),
            'audit_30d'=>self::countSince('logs_auditoria','data_hora',30),
            'emails_30d'=>self::countSince('communication_email_logs','created_at',30),
            'email_failures_30d'=>self::countWhereSince('communication_email_logs','created_at','status','failed',30),
            'directory_users'=>self::count('identity_directory_users'),
            'directory_groups'=>self::count('identity_directory_groups'),
            'whatsapp_30d'=>self::countSince('whatsapp_messages','created_at',30),
        ];
    }

    public static function operational(int $days=30): array
    {
        $days=max(1,min($days,365));
        $errors=[]; $audit=[]; $emails=[];
        if(Schema::hasTable('logs_erros')) $errors=DB::table('logs_erros')->select('modulo',DB::raw('COUNT(*) total'))->where('data_hora','>=',now()->subDays($days))->groupBy('modulo')->orderByDesc('total')->limit(20)->get()->map(fn($r)=>(array)$r)->all();
        if(Schema::hasTable('logs_auditoria')) $audit=DB::table('logs_auditoria')->select('acao',DB::raw('COUNT(*) total'))->where('data_hora','>=',now()->subDays($days))->groupBy('acao')->orderByDesc('total')->limit(20)->get()->map(fn($r)=>(array)$r)->all();
        if(Schema::hasTable('communication_email_logs')) $emails=DB::table('communication_email_logs')->select('status',DB::raw('COUNT(*) total'))->where('created_at','>=',now()->subDays($days))->groupBy('status')->orderByDesc('total')->get()->map(fn($r)=>(array)$r)->all();
        return ['days'=>$days,'errors_by_module'=>$errors,'audit_by_action'=>$audit,'emails_by_status'=>$emails];
    }

    private static function count(string $table): int { return Schema::hasTable($table)?DB::table($table)->count():0; }
    private static function countSince(string $table,string $column,int $days): int { return Schema::hasTable($table)?DB::table($table)->where($column,'>=',now()->subDays($days))->count():0; }
    private static function countWhereSince(string $table,string $column,string $whereColumn,string $value,int $days): int { return Schema::hasTable($table)?DB::table($table)->where($whereColumn,$value)->where($column,'>=',now()->subDays($days))->count():0; }
}
