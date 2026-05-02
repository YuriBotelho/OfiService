<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$pdo = conectar();

$counts = $pdo->query(
    "SELECT
       SUM(status='aberta')    AS abertas,
       SUM(status='andamento') AS andamento,
       SUM(status='concluida') AS concluidas,
       SUM(status='cancelada') AS canceladas,
       COUNT(*)                AS total
     FROM ordens_servico"
)->fetch();

$receita = $pdo->query(
    "SELECT COALESCE(SUM(total), 0) AS receita
     FROM ordens_servico
     WHERE status IN ('concluida','andamento')
       AND MONTH(data_abertura) = MONTH(CURDATE())
       AND YEAR(data_abertura)  = YEAR(CURDATE())"
)->fetchColumn();

$receitaFormatada = number_format($receita / 1000, 1, ',', '.') . 'k';

jsonResposta(true, [
    'abertas'    => (int)$counts['abertas'],
    'andamento'  => (int)$counts['andamento'],
    'concluidas' => (int)$counts['concluidas'],
    'canceladas' => (int)$counts['canceladas'],
    'total'      => (int)$counts['total'],
    'receita_mes'=> $receitaFormatada,
]);
