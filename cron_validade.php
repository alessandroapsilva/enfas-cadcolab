<?php
date_default_timezone_set('America/Sao_Paulo');
require_once '../autoatendimento/config/database.php';
$conn->query("SET time_zone = '-03:00'");

function dispararAviso($conn, $telefone, $msg) {
    $cfg = []; $res = $conn->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('wp_token', 'wp_phone_id')"); 
    while($row = $res->fetch_assoc()) { $cfg[$row['chave']] = trim($row['valor']); }
    if(empty($cfg['wp_token'])) return;
    $tel_format = '55' . preg_replace('/[^0-9]/', '', $telefone);
    $payload = json_encode(["messaging_product"=>"whatsapp","to"=>$tel_format,"type"=>"text","text"=>["body"=>$msg]]);
    $ch = curl_init("https://graph.facebook.com/v18.0/" . $cfg['wp_phone_id'] . "/messages");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $cfg['wp_token'], "Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch); curl_close($ch);
}

$limite_dias = (int)($conn->query("SELECT valor FROM configuracoes WHERE chave='dias_validade_senha'")->fetch_assoc()['valor'] ?? 90);
$tpl = $conn->query("SELECT valor FROM configuracoes WHERE chave='tpl_aviso_validade'")->fetch_assoc()['valor'] ?? '';

if(!empty($tpl)) {
    $sql = "SELECT id, nome_completo, telefone, DATEDIFF(NOW(), data_ultima_senha) as dias_passados FROM pre_registros WHERE status='ativo' AND telefone != '' AND data_ultima_senha IS NOT NULL";
    $res = $conn->query($sql);
    while($colab = $res->fetch_assoc()) {
        $dias_restantes = $limite_dias - $colab['dias_passados'];
        if($dias_restantes == 5 || $dias_restantes == 1 || $dias_restantes <= 0) {
            $msg_pronta = str_replace(['{NOME}', '{DIAS}'], [$colab['nome_completo'], $dias_restantes], $tpl);
            dispararAviso($conn, $colab['telefone'], $msg_pronta);
        }
    }
}
echo "Robô concluído.";
