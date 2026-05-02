<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d = pegarJson();

if (empty($d['data_abertura'])) {
    jsonResposta(false, [], 'Data de abertura é obrigatória.');
}

$pdo = conectar();
$pdo->beginTransaction();

try {
    $id     = !empty($d['id']) ? (int)$d['id'] : null;
    $itens  = $d['itens'] ?? [];

    /* Calcula total */
    $total = 0;
    foreach ($itens as $it) {
        $total += (float)($it['quantidade'] ?? 1) * (float)($it['valor_unitario'] ?? 0);
    }
    $total -= (float)($d['desconto'] ?? 0);
    if ($total < 0) $total = 0;

    $campos = [
        'status'         => $d['status']          ?? 'aberta',
        'cliente_id'     => $d['cliente_id']       ?: null,
        'veiculo_modelo' => $d['veiculo_modelo']   ?? null,
        'veiculo_placa'  => $d['veiculo_placa']    ?? null,
        'responsavel_id' => $d['responsavel_id']   ?: null,
        'atendente_id'   => $d['atendente_id']     ?: null,
        'data_abertura'  => $d['data_abertura'],
        'data_entrega'   => $d['data_entrega']     ?: null,
        'km_atual'       => $d['km_atual']         ?: null,
        'obs'            => $d['obs']              ?? null,
        'desconto'       => (float)($d['desconto'] ?? 0),
        'total'          => $total,
    ];

    if ($id) {
        /* UPDATE */
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        $stmt = $pdo->prepare("UPDATE ordens_servico SET $set WHERE id = ?");
        $stmt->execute([...array_values($campos), $id]);
    } else {
        /* Gera número automático */
        $prefixo   = $pdo->query("SELECT valor FROM configuracoes WHERE chave='prefixo_os'")->fetchColumn() ?: 'OS-';
        $proximoRaw= $pdo->query("SELECT valor FROM configuracoes WHERE chave='proximo_os'")->fetchColumn() ?: '1';
        $numero    = str_pad((int)$proximoRaw, 3, '0', STR_PAD_LEFT);

        $campos['numero'] = $numero;
        $keys   = implode(', ', array_keys($campos));
        $places = implode(', ', array_fill(0, count($campos), '?'));
        $stmt   = $pdo->prepare("INSERT INTO ordens_servico ($keys) VALUES ($places)");
        $stmt->execute(array_values($campos));
        $id = (int)$pdo->lastInsertId();

        /* Incrementa próximo número */
        $pdo->exec("UPDATE configuracoes SET valor = " . ((int)$proximoRaw + 1) . " WHERE chave='proximo_os'");
    }

    /* Recria itens */
    $pdo->prepare("DELETE FROM servicos_os WHERE os_id = ?")->execute([$id]);
    $stmtItem = $pdo->prepare(
        "INSERT INTO servicos_os (os_id, descricao, tipo, quantidade, valor_unitario)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($itens as $it) {
        if (empty(trim($it['descricao'] ?? ''))) continue;
        $stmtItem->execute([
            $id,
            $it['descricao'],
            $it['tipo']            ?? 'servico',
            (float)($it['quantidade']     ?? 1),
            (float)($it['valor_unitario'] ?? 0),
        ]);
    }

    $pdo->commit();
    jsonResposta(true, ['id' => $id]);

} catch (Exception $e) {
    $pdo->rollBack();
    jsonResposta(false, [], 'Erro ao salvar OS: ' . $e->getMessage());
}
