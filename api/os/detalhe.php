<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonResposta(false, [], 'ID inválido.');

$pdo = conectar();

$stmt = $pdo->prepare(
    "SELECT os.*,
            c.nome AS cliente_nome,
            u1.nome AS responsavel_nome,
            u2.nome AS atendente_nome
     FROM ordens_servico os
     LEFT JOIN clientes c  ON c.id  = os.cliente_id
     LEFT JOIN usuarios u1 ON u1.id = os.responsavel_id
     LEFT JOIN usuarios u2 ON u2.id = os.atendente_id
     WHERE os.id = ? LIMIT 1"
);
$stmt->execute([$id]);
$os = $stmt->fetch();

if (!$os) jsonResposta(false, [], 'OS não encontrada.');

$stmtItens = $pdo->prepare(
    "SELECT * FROM servicos_os WHERE os_id = ? ORDER BY id"
);
$stmtItens->execute([$id]);
$os['itens'] = $stmtItens->fetchAll();

jsonResposta(true, ['os' => $os]);
