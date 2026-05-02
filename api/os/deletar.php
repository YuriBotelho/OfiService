<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d  = pegarJson();
$id = (int)($d['id'] ?? 0);
if (!$id) jsonResposta(false, [], 'ID inválido.');

$pdo  = conectar();
$stmt = $pdo->prepare("DELETE FROM ordens_servico WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount()) {
    jsonResposta(true, []);
} else {
    jsonResposta(false, [], 'OS não encontrada.');
}
