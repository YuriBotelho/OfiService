<?php
require 'config/conexao.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($login && $senha) {
        try {
            $pdo  = conectar();
            $stmt = $pdo->prepare(
                "SELECT id, nome, senha_hash FROM usuarios
                 WHERE (email = ? OR nome = ?) AND status = 'ativo' LIMIT 1"
            );
            $stmt->execute([$login, $login]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id']   = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Usuário ou senha inválidos.';
            }
        } catch (Exception $e) {
            $erro = 'Erro ao conectar ao banco de dados. Verifique as configurações em config/conexao.php.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OfiService – Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0d0f14; --surface: #161920; --surface2: #1e2230; --surface3: #262b3a;
    --accent: #f97316; --accent2: #fb923c; --accent-glow: rgba(249,115,22,0.18);
    --text: #f1f3f8; --muted: #7a8099; --border: #2a2f42;
    --success: #22c55e; --danger: #ef4444;
    --radius: 14px; --radius-sm: 8px;
    --font-head: 'Syne', sans-serif; --font-body: 'DM Sans', sans-serif;
  }
  html.light {
    --bg: #f4f5f7; --surface: #ffffff; --surface2: #f0f1f4; --surface3: #e4e6ed;
    --accent: #ea6c0a; --accent2: #f97316; --accent-glow: rgba(234,108,10,0.14);
    --text: #151820; --muted: #6b7280; --border: #d8dbe6;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; background: var(--bg); color: var(--text); font-family: var(--font-body); font-size: 15px; }
  .page-login {
    min-height: 100dvh; display: flex; justify-content: center; align-items: center; padding: 24px;
    background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(249,115,22,0.13) 0%, transparent 70%), var(--bg);
  }
  html.light .page-login { background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(234,108,10,0.1) 0%, transparent 70%), var(--bg); }
  .login-card {
    width: 100%; max-width: 380px; background: var(--surface);
    border: 1px solid var(--border); border-radius: 20px; padding: 36px 28px;
    box-shadow: 0 0 60px rgba(249,115,22,0.07);
  }
  .brand-logo {
    width: 54px; height: 54px; background: var(--accent); border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-head); font-size: 22px; font-weight: 800; color: #fff;
    box-shadow: 0 4px 20px rgba(249,115,22,0.35);
  }
  .login-title { font-family: var(--font-head); font-size: 26px; font-weight: 800; color: var(--text); }
  .login-sub { color: var(--muted); font-size: 13.5px; margin-bottom: 28px; }
  .form-label { font-size: 12.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; display: block; }
  .form-control {
    width: 100%; background: var(--surface2); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text); font-family: var(--font-body);
    font-size: 14.5px; padding: 11px 14px; outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); background: var(--surface2); }
  .form-control::placeholder { color: var(--muted); }
  .btn-primary-custom {
    background: var(--accent); border: none; border-radius: var(--radius-sm);
    color: #fff; font-family: var(--font-body); font-weight: 600;
    font-size: 14.5px; padding: 12px 20px; width: 100%; cursor: pointer;
    box-shadow: 0 4px 16px rgba(249,115,22,0.3); transition: background .2s, transform .1s;
  }
  .btn-primary-custom:hover { background: var(--accent2); }
  .btn-primary-custom:active { transform: scale(0.98); }
  .btn-icon {
    background: var(--surface2); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text);
    width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 17px; flex-shrink: 0;
    transition: background .2s, border-color .2s;
  }
  .btn-icon:hover { background: var(--surface3); border-color: var(--accent); color: var(--accent); }
  .alert-erro {
    background: rgba(239,68,68,0.1); border: 1px solid var(--danger);
    border-radius: var(--radius-sm); padding: 10px 14px;
    color: var(--danger); font-size: 13.5px; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .text-muted-custom { color: var(--muted); font-size: 13px; }
  .theme-toggle {
    width: 44px; height: 24px; background: var(--surface3); border: 1.5px solid var(--border);
    border-radius: 12px; position: relative; cursor: pointer; transition: background .3s, border-color .3s;
  }
  .theme-toggle::after {
    content: ''; position: absolute; left: 3px; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px; border-radius: 50%; background: var(--accent);
    transition: left .28s cubic-bezier(.32,.72,0,1); box-shadow: 0 1px 4px rgba(0,0,0,0.25);
  }
  html.light .theme-toggle::after { left: calc(100% - 19px); }
  html.light .theme-toggle { background: var(--accent-glow); border-color: var(--accent); }
</style>
</head>
<body>
<div class="page-login">
  <div class="login-card">
    <div class="d-flex justify-content-between align-items-start mb-4">
      <div class="brand-logo">OS</div>
      <button class="btn-icon" onclick="toggleTheme()" title="Alternar tema">
        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
      </button>
    </div>
    <div class="login-title">OfiService</div>
    <div class="login-sub">Sistema de Gestão de Ordens de Serviço</div>

    <?php if ($erro): ?>
    <div class="alert-erro">
      <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label class="form-label">E-mail ou usuário</label>
        <input type="text" class="form-control" name="login"
               placeholder="admin@oficina.com.br"
               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label">Senha</label>
        <div style="position:relative">
          <input type="password" class="form-control" name="senha" id="senhaInput"
                 placeholder="••••••••" required>
          <i class="bi bi-eye" id="eyeIcon"
             style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted)"
             onclick="togglePass()"></i>
        </div>
      </div>
      <button type="submit" class="btn-primary-custom">
        <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
      </button>
    </form>
    <p class="text-center mt-3 text-muted-custom">
      Primeiro acesso? Execute <strong>database/criar_admin.php</strong>
    </p>
  </div>
</div>
<script>
let isLight = false;
function toggleTheme() {
  isLight = !isLight;
  document.documentElement.classList.toggle('light', isLight);
  localStorage.setItem('oficina-theme', isLight ? 'light' : 'dark');
  document.getElementById('themeIcon').className = isLight ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
}
function togglePass() {
  const inp = document.getElementById('senhaInput');
  const ico = document.getElementById('eyeIcon');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
(function() {
  const saved = localStorage.getItem('oficina-theme');
  if (saved === 'light') { isLight = true; document.documentElement.classList.add('light'); document.getElementById('themeIcon').className = 'bi bi-sun-fill'; }
})();
</script>
</body>
</html>
