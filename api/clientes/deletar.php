<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d  = pegarJson();
$id = (int)($d['id'] ?? 0);
if (!$id) jsonResposta(false, [], 'ID inválido.');

$pdo  = conectar();
$stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
$stmt->execute([$id]);

jsonResposta($stmt->rowCount() > 0, [], 'Cliente não encontrado.');
