<?php
define('IS_API', true);
require '../../config/conexao.php';
verificarSessao();

$d   = pegarJson();
$pdo = conectar();

$map = [
    'nome'       => 'nome',
    'cnpj'       => 'cnpj',
    'endereco'   => 'endereco',
    'cidade'     => 'cidade',
    'estado'     => 'estado',
    'telefone'   => 'telefone',
    'email'      => 'email',
    'prefixo'    => 'prefixo_os',
    'proximo_os' => 'proximo_os',
    'mensagem'   => 'mensagem_os',
];

$stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
foreach ($map as $campo => $chave) {
    if (isset($d[$campo])) {
        $stmt->execute([$chave, $d[$campo]]);
    }
}

jsonResposta(true, []);
