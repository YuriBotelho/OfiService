<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$pdo    = conectar();
$status = $_GET['status'] ?? 'todas';
$busca  = trim($_GET['busca'] ?? '');

$sql = "SELECT
          os.id, os.numero, os.status,
          os.veiculo_modelo, os.veiculo_placa,
          os.data_abertura, os.total,
          c.nome  AS cliente_nome,
          u.nome  AS responsavel_nome
        FROM ordens_servico os
        LEFT JOIN clientes c ON c.id = os.cliente_id
        LEFT JOIN usuarios u ON u.id = os.responsavel_id
        WHERE 1=1";

$params = [];

if ($status !== 'todas') {
    $sql .= " AND os.status = ?";
    $params[] = $status;
}

if ($busca !== '') {
    $sql .= " AND (os.numero LIKE ? OR c.nome LIKE ? OR os.veiculo_placa LIKE ? OR os.veiculo_modelo LIKE ?)";
    $like = "%$busca%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$sql .= " ORDER BY os.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

jsonResposta(true, ['os' => $rows]);
