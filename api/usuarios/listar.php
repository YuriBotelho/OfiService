<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$pdo  = conectar();
$rows = $pdo->query(
    "SELECT id, nome, email, perfil, telefone, status, created_at
     FROM usuarios
     ORDER BY nome ASC"
)->fetchAll();

jsonResposta(true, ['usuarios' => $rows]);
