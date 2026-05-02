<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'oficina_os');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function verificarSessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['usuario_id'])) {
        if (defined('IS_API')) {
            jsonResposta(false, [], 'Sessão expirada. Faça login novamente.');
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

function jsonResposta(bool $ok, array $dados = [], string $erro = ''): void {
    header('Content-Type: application/json; charset=utf-8');
    $resposta = ['ok' => $ok];
    if ($ok) {
        $resposta = array_merge($resposta, $dados);
    } else {
        $resposta['erro'] = $erro;
    }
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function pegarJson(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}
