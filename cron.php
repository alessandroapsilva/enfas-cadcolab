<?php
// /var/www/cadcolab/cron.php
// ATENÇÃO: ESTE ARQUIVO DEVE SER RODADO VIA TERMINAL NO UBUNTU (CRONTAB) E NÃO PELO NAVEGADOR
if (php_sapi_name() !== 'cli') { die("Acesso negado. Apenas via CLI."); }

date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/../autoatendimento/config/database.php';

$cfg = [];
$q_cfg = $conn->query("SELECT chave, valor FROM configuracoes");
while($row = $q_cfg->fetch_assoc()){ $cfg[$row['chave']] = trim($row['valor']); }

$dias_validade = (int)($cfg['dias_validade_senha'] ?? 90);
$wp_token = $cfg['wp_token'] ?? '';
$wp_phone_id = $cfg['wp_phone_id'] ?? '';
$tpl_aviso = $cfg['tpl_aviso_validade'] ?? 'aviso_senha_expirando_enfas';

echo "[".date('Y-m-d H:i:s')."] Iniciando varredura de automação Enfas...\n";

// 1. VERIFICAR SENHAS EXPIRANDO EM 5 DIAS
$data_corte = date('Y-m-d', strtotime("-".($dias_validade - 5)." days"));
$q_expirando = $conn->query("SELECT id, nome_completo, telefone, username_criado FROM pre_registros WHERE status='ativo' AND DATE(data_ultima_senha) = '$data_corte'");

if($q_expirando && $q_expirando->num_rows > 0) {
    while($u = $q_expirando->fetch_assoc()) {
        if(!empty($u['telefone']) && !empty($wp_token) && !empty($wp_phone_id)) {
            $tel = '55' . preg_replace('/[^0-9]/', '', $u['telefone']);
            $primeiro_nome = explode(' ', $u['nome_completo'])[0];
            
            $payload = json_encode([
                "messaging_product" => "whatsapp", "to" => $tel, "type" => "template",
                "template" => [
                    "name" => $tpl_aviso, "language" => ["code" => "pt_BR"],
                    "components" => [
                        [ "type" => "body", "parameters" => [
                            ["type" => "text", "text" => $primeiro_nome],
                            ["type" => "text", "text" => "5"] // Dias restantes
                        ]]
                    ]
                ]
            ]);

            $ch = curl_init("https://graph.facebook.com/v18.0/$wp_phone_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $wp_token", "Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_exec($ch); curl_close($ch);
            
            echo "Aviso de expiração enviado para {$u['username_criado']}\n";
        }
    }
} else {
    echo "Nenhuma senha a expirar em 5 dias.\n";
}

echo "[".date('Y-m-d H:i:s')."] Varredura concluída.\n";
?>
