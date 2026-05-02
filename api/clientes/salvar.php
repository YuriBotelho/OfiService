<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d    = pegarJson();
$nome = trim($d['nome'] ?? '');

if (!$nome) jsonResposta(false, [], 'Nome do cliente é obrigatório.');

$pdo = conectar();
$pdo->beginTransaction();

try {
    $id = !empty($d['id']) ? (int)$d['id'] : null;

    $campos = [
        'nome'      => $nome,
        'cpf_cnpj'  => $d['cpf_cnpj']  ?? null,
        'telefone'  => $d['telefone']   ?? null,
        'email'     => $d['email']      ?? null,
        'endereco'  => $d['endereco']   ?? null,
        'data_nasc' => $d['data_nasc']  ?: null,
        'obs'       => $d['obs']        ?? null,
    ];

    if ($id) {
        $set  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        $stmt = $pdo->prepare("UPDATE clientes SET $set WHERE id = ?");
        $stmt->execute([...array_values($campos), $id]);
    } else {
        $keys   = implode(', ', array_keys($campos));
        $places = implode(', ', array_fill(0, count($campos), '?'));
        $stmt   = $pdo->prepare("INSERT INTO clientes ($keys) VALUES ($places)");
        $stmt->execute(array_values($campos));
        $id = (int)$pdo->lastInsertId();
    }

    /* Recria veículos */
    $pdo->prepare("DELETE FROM veiculos WHERE cliente_id = ?")->execute([$id]);
    $stmtV = $pdo->prepare("INSERT INTO veiculos (cliente_id, modelo, placa) VALUES (?, ?, ?)");
    foreach (($d['veiculos'] ?? []) as $v) {
        $modelo = trim($v['modelo'] ?? '');
        if (!$modelo) continue;
        $stmtV->execute([$id, $modelo, $v['placa'] ?? '']);
    }

    $pdo->commit();
    jsonResposta(true, ['id' => $id]);

} catch (Exception $e) {
    $pdo->rollBack();
    jsonResposta(false, [], 'Erro ao salvar cliente: ' . $e->getMessage());
}
