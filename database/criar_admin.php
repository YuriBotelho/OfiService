<?php
/**
 * SETUP INICIAL — Execute uma única vez no navegador:
 *   http://localhost/seu-projeto/database/criar_admin.php
 *
 * Este script cria o usuário administrador com senha "1234"
 * e insere OS de exemplo (requer que clientes já existam via SQL).
 *
 * APAGUE este arquivo após o setup por segurança!
 */

require __DIR__ . '/../config/conexao.php';

$log = [];

try {
    $pdo = conectar();

    /* ── Usuários ── */
    $usuarios = [
        ['Administrador',    'admin@oficina.com.br',  '1234', 'admin'],
        ['João Mecânico',    'joao@oficina.com.br',   '1234', 'mecanico'],
        ['Pedro Técnico',    'pedro@oficina.com.br',  '1234', 'mecanico'],
        ['Maria Recepção',   'maria@oficina.com.br',  '1234', 'atendente'],
        ['Lucas Eletricista','lucas@oficina.com.br',  '1234', 'mecanico'],
    ];

    $stmtU = $pdo->prepare(
        "INSERT IGNORE INTO usuarios (nome, email, senha_hash, perfil, telefone, status)
         VALUES (?, ?, ?, ?, ?, 'ativo')"
    );
    $telefones = ['(11) 9 0000-0001','(11) 9 0000-0002','(11) 9 0000-0003','(11) 9 0000-0004','(11) 9 0000-0005'];
    foreach ($usuarios as $i => $u) {
        $hash = password_hash($u[2], PASSWORD_DEFAULT);
        $stmtU->execute([$u[0], $u[1], $hash, $u[3], $telefones[$i]]);
        $log[] = "Usuário criado: {$u[0]} ({$u[1]}) — senha: {$u[2]}";
    }

    /* ── IDs para OS de exemplo ── */
    $adminId  = $pdo->query("SELECT id FROM usuarios WHERE email='admin@oficina.com.br' LIMIT 1")->fetchColumn();
    $joaoId   = $pdo->query("SELECT id FROM usuarios WHERE email='joao@oficina.com.br' LIMIT 1")->fetchColumn();
    $pedroId  = $pdo->query("SELECT id FROM usuarios WHERE email='pedro@oficina.com.br' LIMIT 1")->fetchColumn();
    $lucasId  = $pdo->query("SELECT id FROM usuarios WHERE email='lucas@oficina.com.br' LIMIT 1")->fetchColumn();

    $carlosId  = $pdo->query("SELECT id FROM clientes WHERE nome='Carlos Menezes' LIMIT 1")->fetchColumn();
    $fernandaId= $pdo->query("SELECT id FROM clientes WHERE nome='Fernanda Lima' LIMIT 1")->fetchColumn();
    $rodrigoId = $pdo->query("SELECT id FROM clientes WHERE nome='Rodrigo Santos' LIMIT 1")->fetchColumn();
    $anaId     = $pdo->query("SELECT id FROM clientes WHERE nome='Ana Carvalho' LIMIT 1")->fetchColumn();
    $marcosId  = $pdo->query("SELECT id FROM clientes WHERE nome='Marcos Souza' LIMIT 1")->fetchColumn();

    /* ── OS de exemplo ── */
    $stmtOS = $pdo->prepare(
        "INSERT IGNORE INTO ordens_servico
           (numero, status, cliente_id, veiculo_modelo, veiculo_placa,
            responsavel_id, atendente_id, data_abertura, data_entrega,
            km_atual, obs, desconto, total)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );

    $osExemplo = [
        ['001','andamento',$carlosId, 'Honda Civic 2019',   'ABC-1D23', $joaoId,  $adminId, '2024-06-03','2024-06-05',52430,'Barulho no motor ao acelerar.',0,     480.00],
        ['002','aberta',   $fernandaId,'VW Gol 2015',       'XYZ-9K88', $pedroId, $adminId, '2024-06-02','2024-06-04',38000,'Troca de óleo e filtros.',    0,     210.00],
        ['003','concluida',$rodrigoId, 'Fiat Strada 2022',  'DEF-4R77', $joaoId,  $adminId, '2024-06-02','2024-06-03',14800,'Revisão completa 40k km.',    0,    1250.00],
        ['004','cancelada',$anaId,     'Renault Kwid 2020', 'GHI-5M11', $lucasId, $adminId, '2024-05-30','2024-05-31',22000,'Cliente desistiu do serviço.',0,       0.00],
        ['005','concluida',$marcosId,  'Toyota Corolla 2021','JKL-2N44',$pedroId, $adminId, '2024-05-29','2024-05-29',61000,'Alinhamento e balanceamento.',0,     680.00],
    ];

    foreach ($osExemplo as $os) {
        $stmtOS->execute($os);
        $log[] = "OS criada: #{$os[0]} ({$os[1]})";
    }

    /* ── Itens das OS ── */
    $osIds = [];
    foreach (['001','002','003','004','005'] as $num) {
        $id = $pdo->query("SELECT id FROM ordens_servico WHERE numero='$num' LIMIT 1")->fetchColumn();
        $osIds[$num] = $id;
    }

    $stmtItem = $pdo->prepare(
        "INSERT INTO servicos_os (os_id, descricao, tipo, quantidade, valor_unitario) VALUES (?,?,?,?,?)"
    );

    $itens = [
        [$osIds['001'], 'Revisão motor',    'servico', 1, 200.00],
        [$osIds['001'], 'Filtro de óleo',   'peca',    1,  80.00],
        [$osIds['001'], 'Filtro de ar',     'peca',    1,  60.00],
        [$osIds['001'], 'Mão de obra geral','servico', 2,  70.00],
        [$osIds['002'], 'Troca de óleo',    'servico', 1, 120.00],
        [$osIds['002'], 'Filtro de óleo',   'peca',    1,  90.00],
        [$osIds['003'], 'Revisão 40.000km', 'servico', 1, 850.00],
        [$osIds['003'], 'Kit de filtros',   'peca',    1, 400.00],
        [$osIds['005'], 'Alinhamento',      'servico', 1, 180.00],
        [$osIds['005'], 'Balanceamento 4x', 'servico', 4, 125.00],
    ];
    foreach ($itens as $it) {
        $stmtItem->execute($it);
    }
    $log[] = 'Itens de OS inseridos.';

    /* ── Atualiza próximo número de OS ── */
    $pdo->exec("UPDATE configuracoes SET valor='6' WHERE chave='proximo_os'");
    $log[] = 'Configuração de próximo número de OS atualizada para 6.';

    $status = 'success';
    $msg    = 'Setup concluído com sucesso!';

} catch (Exception $e) {
    $status = 'error';
    $msg    = 'Erro: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>OfiService – Setup</title>
  <style>
    body { font-family: sans-serif; background:#0d0f14; color:#f1f3f8; padding:40px; }
    h1 { color: #f97316; }
    .ok  { color: #22c55e; }
    .err { color: #ef4444; }
    pre  { background:#161920; padding:16px; border-radius:8px; font-size:13px; line-height:1.7; }
    .warn { background:#1e2230; border:1px solid #f97316; border-radius:8px; padding:16px; margin-top:24px; color:#f97316; }
  </style>
</head>
<body>
  <h1>OfiService — Setup Inicial</h1>
  <p class="<?= $status === 'success' ? 'ok' : 'err' ?>"><?= htmlspecialchars($msg) ?></p>
  <pre><?= htmlspecialchars(implode("\n", $log)) ?></pre>
  <?php if ($status === 'success'): ?>
  <div class="warn">
    ⚠️ <strong>Apague este arquivo agora!</strong><br>
    Por segurança, delete <code>database/criar_admin.php</code> antes de colocar o sistema em produção.<br><br>
    <strong>Próximo passo:</strong> Acesse <a href="../login.php" style="color:#f97316">login.php</a> com <code>admin@oficina.com.br</code> / <code>1234</code>
  </div>
  <?php endif; ?>
</body>
</html>
