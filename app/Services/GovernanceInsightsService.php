<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GovernanceInsightsService
{
    public static function generate(): array
    {
        $items=[];
        $push=function($severity,$title,$message,$action,$module) use (&$items){
            $items[]=['severity'=>$severity,'title'=>$title,'message'=>$message,'action'=>$action,'module'=>$module];
        };

        if(Schema::hasTable('logs_erros')){
            $recent=DB::table('logs_erros')->where('data_hora','>=',now()->subDays(7))->count();
            if($recent>=10) $push('high','Volume elevado de falhas','Foram registrados '.$recent.' erros nos últimos 7 dias.','Priorizar os módulos com maior recorrência e revisar credenciais/permissões.','observabilidade');
            elseif($recent>0) $push('medium','Erros recentes detectados','Há '.$recent.' falhas recentes registradas.','Revisar a Central de Erros antes do próximo ciclo de provisionamento.','observabilidade');
        }

        if(Schema::hasTable('communication_email_logs')){
            $failed=DB::table('communication_email_logs')->where('status','failed')->where('created_at','>=',now()->subDays(30))->count();
            if($failed>0) $push($failed>=5?'high':'medium','Falhas de e-mail','Existem '.$failed.' e-mails com falha nos últimos 30 dias.','Validar SMTP, destinatários e reprocessar as comunicações pendentes.','comunicacoes');
        }

        if(Schema::hasTable('identity_directory_users')){
            $stale=DB::table('identity_directory_users')->where(function($q){$q->whereNull('last_synced_at')->orWhere('last_synced_at','<',now()->subDays(2));})->count();
            if($stale>0) $push('medium','Diretório desatualizado',$stale.' identidades estão sem sincronização recente.','Executar sincronização LDAP e revisar objetos que continuam divergentes.','identidade');
        }

        if(Schema::hasTable('identity_directory_settings')){
            $enabled=(bool)DB::table('identity_directory_settings')->value('enabled');
            if(!$enabled) $push('low','LDAP ainda não é a autoridade principal','A autenticação de diretório está desativada.','Após validar usuários e grupos, ativar LDAP e manter fallback local apenas para contingência.','identidade');
        }

        if(Schema::hasTable('whatsapp_messages')){
            $failed=DB::table('whatsapp_messages')->whereIn('status',['failed','error'])->where('created_at','>=',now()->subDays(30))->count();
            if($failed>0) $push('medium','Mensagens WhatsApp com falha',$failed.' mensagens recentes não concluíram o envio.','Revisar templates, token Meta e números de destino.','whatsapp');
        }

        if(!$items) $push('ok','Ambiente saudável','Nenhum risco operacional relevante foi identificado pelas regras locais.','Continuar acompanhando integrações, auditoria e sincronizações.','governanca');

        $score=max(0,100-collect($items)->sum(fn($x)=>match($x['severity']){'high'=>20,'medium'=>10,'low'=>4,default=>0}));
        return ['score'=>$score,'generated_at'=>now()->toIso8601String(),'items'=>$items];
    }
}
