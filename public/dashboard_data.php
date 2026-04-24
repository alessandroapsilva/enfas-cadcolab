<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

/**
 * AJUSTE AQUI se seu arquivo de conexao nao for este:
 * require_once __DIR__ . "/../config/db.php";
 *
 * Este script espera mysqli em $conn.
 */
require_once __DIR__ . "/../config/db.php";

function out(array $payload, int $code = 200): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
  out(["ok" => false, "error" => "Conexao nao encontrada. Ajuste o require e/ou a variavel mysqli \$conn."], 500);
}

mysqli_set_charset($conn, "utf8mb4");

$data = [
  "ok" => true,
  "generated_at" => date("c"),
  "unidades" => [],
  "setores" => [],
  "cargos" => [],
  "status_m365" => []
];

$sqlUnidades = "
  SELECT u.nome_unidade AS label, COUNT(p.id) AS total
  FROM pre_registros p
  INNER JOIN unidades u ON p.unidade_id = u.id
  GROUP BY u.nome_unidade
  ORDER BY total DESC, label ASC
";
if ($res = $conn->query($sqlUnidades)) {
  while ($row = $res->fetch_assoc()) $data[\"unidades\"][] = $row;
  $res->free();
}

$sqlSetores = "
  SELECT s.nome_setor AS label, COUNT(p.id) AS total
  FROM pre_registros p
  INNER JOIN setores s ON p.setor_id = s.id
  GROUP BY s.nome_setor
  ORDER BY total DESC, label ASC
";
if ($res = $conn->query($sqlSetores)) {
  while ($row = $res->fetch_assoc()) $data[\"setores\"][] = $row;
  $res->free();
}

$sqlCargos = "
  SELECT c.nome_cargo AS label, COUNT(p.id) AS total
  FROM pre_registros p
  INNER JOIN cargos c ON p.cargo_id = c.id
  GROUP BY c.nome_cargo
  ORDER BY total DESC, label ASC
";
if ($res = $conn->query($sqlCargos)) {
  while ($row = $res->fetch_assoc()) $data[\"cargos\"][] = $row;
  $res->free();
}

$sqlStatus = "
  SELECT COALESCE(NULLIF(status_m365, ), indefinido) AS label, COUNT(*) AS total
  FROM pre_registros
  GROUP BY label
  ORDER BY total DESC, label ASC
";
if ($res = $conn->query($sqlStatus)) {
  while ($row = $res->fetch_assoc()) $data[\"status_m365\"][] = $row;
  $res->free();
}

out($data);
