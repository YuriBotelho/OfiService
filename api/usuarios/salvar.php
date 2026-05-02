<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d     = pegarJson();
$nome  = trim($d['nome']  ?? '');
$email = trim($d['email'] ?? '');

if (!$nome || !$email) jsonResposta(false, [], 'Nome e e-mail são obrigatórios.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResposta(false, [], 'E-mail inválido.');

$pdo = conectar();
$id  = !empty($d['id']) ? (int)$d['id'] : null;

try {
    $campos = [
        'nome'     => $nome,
        'email'    => $email,
        'perfil'   => in_array($d['perfil'] ?? '', ['admin','mecanico','atendente']) ? $d['perfil'] : 'mecanico',
        'telefone' => $d['telefone'] ?? null,
        'status'   => ($d['status'] ?? 'ativo') === 'inativo' ? 'inativo' : 'ativo',
    ];

    if (!empty($d['senha'])) {
        $campos['senha_hash'] = password_hash($d['senha'], PASSWORD_DEFAULT);
    }

    if ($id) {
        /* UPDATE — não sobrescreve senha se não foi enviada */
        $set  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        $stmt = $pdo->prepare("UPDATE usuarios SET $set WHERE id = ?");
        $stmt->execute([...array_values($campos), $id]);
    } else {
        if (empty($campos['senha_hash'])) {
            jsonResposta(false, [], 'Informe uma senha para o novo usuário.');
        }
        $keys   = implode(', ', array_keys($campos));
        $places = implode(', ', array_fill(0, count($campos), '?'));
        $stmt   = $pdo->prepare("INSERT INTO usuarios ($keys) VALUES ($places)");
        $stmt->execute(array_values($campos));
        $id = (int)$pdo->lastInsertId();
    }

    jsonResposta(true, ['id' => $id]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResposta(false, [], 'Este e-mail já está cadastrado.');
    }
    jsonResposta(false, [], 'Erro ao salvar usuário: ' . $e->getMessage());
}
