<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$pdo   = conectar();
$busca = trim($_GET['busca'] ?? '');

$sql    = "SELECT c.*,
                  (SELECT COUNT(*) FROM ordens_servico WHERE cliente_id = c.id) AS total_os
           FROM clientes c
           WHERE 1=1";
$params = [];

if ($busca !== '') {
    $sql .= " AND (c.nome LIKE ? OR c.cpf_cnpj LIKE ? OR c.telefone LIKE ? OR c.email LIKE ?)";
    $like = "%$busca%";
    $params = [$like, $like, $like, $like];
}

$sql .= " ORDER BY c.nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

/* Busca veículos para cada cliente */
$stmtV = $pdo->prepare("SELECT id, modelo, placa FROM veiculos WHERE cliente_id = ? ORDER BY id");
foreach ($clientes as &$cl) {
    $stmtV->execute([$cl['id']]);
    $cl['veiculos'] = $stmtV->fetchAll();
}
unset($cl);

jsonResposta(true, ['clientes' => $clientes]);
