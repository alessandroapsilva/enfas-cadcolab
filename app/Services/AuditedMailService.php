<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditedMailService
{
    public function send(string $to, string $subject, string $html, array $cfg, ?int $preRegistroId = null, ?string $template = null): bool
    {
        $logId = null;
        if (Schema::hasTable('communication_logs')) {
            $logId = DB::table('communication_logs')->insertGetId([
                'channel' => 'email',
                'direction' => 'outbound',
                'recipient' => $to,
                'subject' => $subject,
                'template' => $template,
                'status' => 'sending',
                'provider' => 'smtp',
                'pre_registro_id' => $preRegistroId,
                'actor' => session('admin_nome', 'Sistema'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            $ok = $this->sendSmtp($to, $subject, $html, $cfg);
            if ($logId) {
                DB::table('communication_logs')->where('id', $logId)->update([
                    'status' => $ok ? 'sent' : 'failed',
                    'sent_at' => $ok ? now() : null,
                    'error_message' => $ok ? null : 'Servidor SMTP não confirmou a entrega para encaminhamento.',
                    'updated_at' => now(),
                ]);
            }
            return $ok;
        } catch (\Throwable $e) {
            if ($logId) DB::table('communication_logs')->where('id', $logId)->update(['status'=>'failed','error_message'=>$e->getMessage(),'updated_at'=>now()]);
            return false;
        }
    }

    private function sendSmtp(string $to, string $subject, string $message, array $cfg): bool
    {
        if (empty($cfg['smtp_host']) || empty($cfg['smtp_user'])) return false;
        $host = trim($cfg['smtp_host']);
        $port = (int)($cfg['smtp_port'] ?? 587);
        $user = trim($cfg['smtp_user']);
        $pass = (string)($cfg['smtp_pass'] ?? '');
        $from = trim($cfg['mail_from'] ?? $user);
        $fromName = trim($cfg['mail_from_name'] ?? 'CADCOLAB ENFAS');

        $context = stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]]);
        $scheme = $port === 465 ? 'ssl://' : 'tcp://';
        $socket = @stream_socket_client($scheme.$host.':'.$port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) return false;
        stream_set_timeout($socket, 15);

        $read = function($s){ $d=''; while($line=@fgets($s,515)){ $d.=$line; if(substr($line,3,1)===' ') break; } return $d; };
        $expect = function($s, array $codes) use ($read){ $r=$read($s); return in_array((int)substr($r,0,3),$codes,true); };

        if (!$expect($socket,[220])) { fclose($socket); return false; }
        fwrite($socket,"EHLO {$host}\r\n"); if (!$expect($socket,[250])) { fclose($socket); return false; }
        if (in_array($port,[25,587],true)) {
            fwrite($socket,"STARTTLS\r\n"); if (!$expect($socket,[220])) { fclose($socket); return false; }
            if (!@stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
            fwrite($socket,"EHLO {$host}\r\n"); if (!$expect($socket,[250])) { fclose($socket); return false; }
        }
        fwrite($socket,"AUTH LOGIN\r\n"); if (!$expect($socket,[334])) { fclose($socket); return false; }
        fwrite($socket,base64_encode($user)."\r\n"); if (!$expect($socket,[334])) { fclose($socket); return false; }
        fwrite($socket,base64_encode($pass)."\r\n"); if (!$expect($socket,[235])) { fclose($socket); return false; }
        fwrite($socket,"MAIL FROM:<{$from}>\r\n"); if (!$expect($socket,[250])) { fclose($socket); return false; }
        fwrite($socket,"RCPT TO:<{$to}>\r\n"); if (!$expect($socket,[250,251])) { fclose($socket); return false; }
        fwrite($socket,"DATA\r\n"); if (!$expect($socket,[354])) { fclose($socket); return false; }

        $headers = "Date: ".date(DATE_RFC2822)."\r\n".
            "From: =?UTF-8?B?".base64_encode($fromName)."?= <{$from}>\r\n".
            "To: <{$to}>\r\n".
            "Subject: =?UTF-8?B?".base64_encode($subject)."?=\r\n".
            "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n";
        $safeBody = preg_replace('/(?m)^\./','..',$message);
        fwrite($socket,$headers.$safeBody."\r\n.\r\n");
        $ok = $expect($socket,[250]);
        fwrite($socket,"QUIT\r\n"); fclose($socket);
        return $ok;
    }
}
