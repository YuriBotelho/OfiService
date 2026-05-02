<?php
require 'config/conexao.php';
verificarSessao();

/* Saudação dinâmica */
$hora     = (int)date('H');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$nomeUsuario = htmlspecialchars($_SESSION['usuario_nome']);

/* Carrega configurações para a página de configurações */
try {
    $pdo = conectar();
    $cfgRows = $pdo->query("SELECT chave, valor FROM configuracoes")->fetchAll();
    $cfg = [];
    foreach ($cfgRows as $r) $cfg[$r['chave']] = $r['valor'];
} catch (Exception $e) {
    $cfg = [];
}
function cfg(string $chave, string $default = ''): string {
    global $cfg;
    return htmlspecialchars($cfg[$chave] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OfiService – Sistema de Gestão</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0d0f14;
    --surface: #161920;
    --surface2: #1e2230;
    --surface3: #262b3a;
    --accent: #f97316;
    --accent2: #fb923c;
    --accent-glow: rgba(249,115,22,0.18);
    --text: #f1f3f8;
    --muted: #7a8099;
    --border: #2a2f42;
    --success: #22c55e;
    --warning: #eab308;
    --danger: #ef4444;
    --info: #3b82f6;
    --radius: 14px;
    --radius-sm: 8px;
    --font-head: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;
    --nav-h: 64px;
    --bottom-h: 68px;
  }

  html.light {
    --bg: #f4f5f7;
    --surface: #ffffff;
    --surface2: #f0f1f4;
    --surface3: #e4e6ed;
    --accent: #ea6c0a;
    --accent2: #f97316;
    --accent-glow: rgba(234,108,10,0.14);
    --text: #151820;
    --muted: #6b7280;
    --border: #d8dbe6;
    --success: #16a34a;
    --warning: #b45309;
    --danger: #dc2626;
    --info: #2563eb;
  }

  .theme-toggle {
    width: 44px; height: 24px;
    background: var(--surface3);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    transition: background .3s, border-color .3s;
    flex-shrink: 0;
  }
  .theme-toggle::after {
    content: '';
    position: absolute;
    left: 3px; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px;
    border-radius: 50%;
    background: var(--accent);
    transition: left .28s cubic-bezier(.32,.72,0,1), background .3s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.25);
  }
  html.light .theme-toggle::after { left: calc(100% - 19px); }
  html.light .theme-toggle { background: var(--accent-glow); border-color: var(--accent); }

  .theme-icon { font-size: 15px; color: var(--muted); transition: color .3s; }

  html.light .card-custom { box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
  html.light .modal-sheet { box-shadow: 0 -4px 32px rgba(0,0,0,0.1); }
  html.light .bottom-nav  { box-shadow: 0 -1px 12px rgba(0,0,0,0.07); }
  html.light .topnav      { box-shadow: 0 1px 8px rgba(0,0,0,0.06); }
  html.light .toast-msg   { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
  *, *::before, *::after {
    transition: background-color .28s ease, border-color .28s ease, color .18s ease, box-shadow .28s ease;
  }
  .page, .modal-sheet, .bottom-nav, .topnav { transition: background-color .28s ease, border-color .28s ease; }
  html.light .form-select option { background: #ffffff; color: #151820; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  html, body {
    height: 100%;
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-body);
    font-size: 15px;
    overscroll-behavior: none;
  }

  .page { display: none; min-height: 100dvh; flex-direction: column; }
  .page.active { display: flex; }

  .form-label { font-size: 12.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
  .form-control, .form-select {
    background: var(--surface2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text);
    font-family: var(--font-body);
    font-size: 14.5px;
    padding: 11px 14px;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-control:focus, .form-select:focus {
    background: var(--surface2);
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-glow);
    color: var(--text);
    outline: none;
  }
  .form-control::placeholder { color: var(--muted); }
  .form-select option { background: var(--surface2); }

  .btn-primary-custom {
    background: var(--accent);
    border: none; border-radius: var(--radius-sm);
    color: #fff; font-family: var(--font-body); font-weight: 600;
    font-size: 14.5px; padding: 12px 20px; width: 100%;
    cursor: pointer; transition: background .2s, transform .1s, box-shadow .2s;
    box-shadow: 0 4px 16px rgba(249,115,22,0.3);
  }
  .btn-primary-custom:hover { background: var(--accent2); box-shadow: 0 6px 20px rgba(249,115,22,0.4); }
  .btn-primary-custom:active { transform: scale(0.98); }

  .btn-ghost {
    background: var(--surface2); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text);
    font-family: var(--font-body); font-weight: 500;
    font-size: 14px; padding: 10px 16px; cursor: pointer;
    transition: background .2s, border-color .2s;
  }
  .btn-ghost:hover { background: var(--surface3); border-color: var(--accent); }

  .btn-danger-custom {
    background: transparent; border: 1.5px solid var(--danger);
    border-radius: var(--radius-sm); color: var(--danger);
    font-size: 13px; padding: 8px 14px; cursor: pointer;
    transition: background .2s;
  }
  .btn-danger-custom:hover { background: rgba(239,68,68,0.1); }

  .btn-icon {
    background: var(--surface2); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text);
    width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 17px; transition: background .2s, border-color .2s;
    flex-shrink: 0;
  }
  .btn-icon:hover { background: var(--surface3); border-color: var(--accent); color: var(--accent); }
  .btn-icon.accent { background: var(--accent); border-color: var(--accent); color: #fff; }
  .btn-icon.accent:hover { background: var(--accent2); }

  .topnav {
    height: var(--nav-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 16px;
    position: sticky; top: 0; z-index: 100;
    flex-shrink: 0;
  }
  .topnav-logo {
    width: 36px; height: 36px;
    background: var(--accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-head); font-size: 16px; font-weight: 800; color: #fff;
    margin-right: 12px; flex-shrink: 0;
  }
  .topnav-title { font-family: var(--font-head); font-size: 18px; font-weight: 700; flex: 1; }
  .topnav-sub { font-size: 11px; color: var(--muted); font-weight: 400; display: block; margin-top: -2px; }

  .bottom-nav {
    height: var(--bottom-h);
    background: var(--surface);
    border-top: 1px solid var(--border);
    display: flex; align-items: stretch;
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
    padding-bottom: env(safe-area-inset-bottom);
  }
  .nav-item {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
    cursor: pointer; gap: 4px;
    color: var(--muted); transition: color .2s;
    font-size: 10.5px; font-weight: 500;
    border-top: 2px solid transparent;
    padding-top: 2px;
  }
  .nav-item i { font-size: 20px; line-height: 1; }
  .nav-item.active { color: var(--accent); border-top-color: var(--accent); }
  .nav-item.active i { text-shadow: 0 0 12px var(--accent); }

  .content {
    flex: 1; overflow-y: auto;
    padding: 16px 16px calc(var(--bottom-h) + 16px);
  }

  .card-custom {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
    margin-bottom: 12px;
    transition: border-color .2s, box-shadow .2s;
  }
  .card-custom:hover { border-color: var(--surface3); }
  .card-custom.clickable { cursor: pointer; }
  .card-custom.clickable:hover { border-color: var(--accent); box-shadow: 0 2px 16px rgba(249,115,22,0.08); }

  .stat-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; margin-bottom: 16px; }
  .stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px;
  }
  .stat-card.accent-border { border-color: var(--accent); }
  .stat-num { font-family: var(--font-head); font-size: 28px; font-weight: 800; line-height: 1; }
  .stat-label { font-size: 11.5px; color: var(--muted); margin-top: 4px; }

  .badge-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600; white-space: nowrap;
  }
  .badge-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
  .badge-aberta    { background: rgba(59,130,246,0.12); color: var(--info); }
  .badge-andamento { background: rgba(234,179,8,0.12);  color: var(--warning); }
  .badge-concluida { background: rgba(34,197,94,0.12);  color: var(--success); }
  .badge-cancelada { background: rgba(239,68,68,0.12);  color: var(--danger); }

  .search-wrap { position: relative; margin-bottom: 14px; }
  .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px; }
  .search-wrap input { padding-left: 38px; }

  .sec-title { font-family: var(--font-head); font-size: 13px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; margin-top: 6px; }

  .modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 200;
    background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
    align-items: flex-end; justify-content: center;
  }
  .modal-overlay.active { display: flex; }
  .modal-sheet {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px 20px 0 0;
    width: 100%; max-width: 480px;
    max-height: 92dvh;
    overflow-y: auto;
    padding: 20px 20px 32px;
    animation: slideUp .28s cubic-bezier(.32,.72,0,1);
  }
  @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
  .modal-handle { width: 40px; height: 4px; background: var(--border); border-radius: 4px; margin: 0 auto 18px; }

  .divider { height: 1px; background: var(--border); margin: 14px 0; }

  .avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--surface3);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-head); font-size: 14px; font-weight: 700;
    color: var(--accent); flex-shrink: 0;
    border: 2px solid var(--border);
  }

  .toast-wrap { position: fixed; top: 16px; left: 50%; transform: translateX(-50%); z-index: 999; width: calc(100% - 32px); max-width: 380px; }
  .toast-msg {
    background: var(--surface3); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 12px 16px;
    font-size: 13.5px; color: var(--text); display: flex; align-items: center; gap: 10px;
    margin-bottom: 8px; animation: fadeInDown .2s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
  }
  .toast-msg.success { border-left: 3px solid var(--success); }
  .toast-msg.error   { border-left: 3px solid var(--danger); }
  @keyframes fadeInDown { from { opacity:0; transform: translateY(-12px); } to { opacity:1; transform: translateY(0); } }

  .service-row {
    display: flex; align-items: center; gap: 10px;
    background: var(--surface2); border-radius: var(--radius-sm);
    padding: 10px 12px; margin-bottom: 8px;
  }
  .service-row input { flex: 1; min-width: 0; }
  .service-row .qty, .service-row .price { width: 68px; }

  .tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--surface3); border: 1px solid var(--border);
    border-radius: 6px; padding: 3px 8px;
    font-size: 12px; color: var(--muted);
  }

  .settings-section { margin-bottom: 24px; }
  .settings-section-title {
    font-family: var(--font-head); font-size: 11px; font-weight: 700;
    color: var(--accent); text-transform: uppercase; letter-spacing: 1.2px;
    margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border);
  }

  @media (min-width: 480px) { .stat-grid { grid-template-columns: repeat(4,1fr); } }

  .tab-row { display: flex; gap: 6px; margin-bottom: 14px; overflow-x: auto; padding-bottom: 2px; }
  .tab-row::-webkit-scrollbar { display: none; }
  .tab-btn {
    flex-shrink: 0; padding: 7px 14px;
    background: var(--surface2); border: 1.5px solid var(--border);
    border-radius: 20px; font-size: 13px; font-weight: 500;
    color: var(--muted); cursor: pointer; transition: all .18s;
  }
  .tab-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }

  .fab {
    position: fixed; right: 20px;
    bottom: calc(var(--bottom-h) + 16px);
    width: 52px; height: 52px;
    background: var(--accent);
    border-radius: 16px; border: none;
    color: #fff; font-size: 24px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 90;
    box-shadow: 0 4px 20px rgba(249,115,22,0.4);
    transition: transform .15s, box-shadow .15s;
  }
  .fab:hover { transform: scale(1.07); box-shadow: 0 6px 28px rgba(249,115,22,0.5); }
  .fab:active { transform: scale(0.96); }

  .text-accent { color: var(--accent); }
  .text-muted-custom { color: var(--muted); font-size: 13px; }
  .fw-head { font-family: var(--font-head); font-weight: 700; }
  .total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; }
  .total-row.grand { font-family: var(--font-head); font-size: 17px; font-weight: 700; border-top: 1.5px solid var(--border); padding-top: 12px; margin-top: 4px; }

  .user-role-badge { font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
  .role-admin    { background: rgba(249,115,22,0.15); color: var(--accent); }
  .role-mecanico { background: rgba(59,130,246,0.13); color: var(--info); }
  .role-atendente{ background: rgba(34,197,94,0.13);  color: var(--success); }

  .empty-state { text-align: center; padding: 48px 24px; color: var(--muted); }
  .empty-state i { font-size: 48px; margin-bottom: 12px; opacity: .4; display: block; }
  .empty-state p { font-size: 14px; }

  .input-prefix-wrap { position: relative; }
  .input-prefix-wrap .prefix { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; }
  .input-prefix-wrap input { padding-left: 30px; }

  .loading-state { text-align: center; padding: 32px; color: var(--muted); font-size: 13px; }
</style>
</head>
<body>

<div class="toast-wrap" id="toastWrap"></div>

<!-- ════ DASHBOARD ════ -->
<div class="page active" id="page-dashboard">
  <div class="topnav">
    <div class="topnav-logo">OS</div>
    <div class="flex-fill">
      <span class="topnav-title">OfiService</span>
      <span class="topnav-sub"><?= $saudacao ?>, <?= $nomeUsuario ?>!</span>
    </div>
    <button class="btn-icon me-2" onclick="toggleTheme()" id="themeBtnDash" title="Alternar tema"><i class="bi bi-moon-stars-fill" id="themeIconDash"></i></button>
    <button class="btn-icon" onclick="goPage('page-settings')" title="Configurações"><i class="bi bi-gear"></i></button>
  </div>
  <div class="content">
    <div class="stat-grid">
      <div class="stat-card accent-border">
        <div class="stat-num text-accent" id="stat-abertas">—</div>
        <div class="stat-label"><i class="bi bi-file-earmark-text me-1"></i>OS Abertas</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:var(--warning)" id="stat-andamento">—</div>
        <div class="stat-label"><i class="bi bi-tools me-1"></i>Em Andamento</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:var(--success)" id="stat-concluidas">—</div>
        <div class="stat-label"><i class="bi bi-check2-circle me-1"></i>Concluídas</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" id="stat-receita">R$&nbsp;<span style="font-size:20px">—</span></div>
        <div class="stat-label"><i class="bi bi-currency-dollar me-1"></i>Mês Atual</div>
      </div>
    </div>

    <div class="sec-title">Últimas Ordens de Serviço</div>
    <div id="dashOsList"><div class="loading-state"><i class="bi bi-hourglass-split"></i> Carregando…</div></div>
    <button class="btn-ghost w-100 mt-2" onclick="goTab('os')">Ver todas as OS <i class="bi bi-arrow-right ms-1"></i></button>
  </div>
</div>

<!-- ════ ORDENS DE SERVIÇO ════ -->
<div class="page" id="page-os">
  <div class="topnav">
    <div class="topnav-logo">OS</div>
    <div class="topnav-title flex-fill">Ordens de Serviço</div>
    <button class="btn-icon accent" onclick="openNewOS()"><i class="bi bi-plus-lg"></i></button>
  </div>
  <div class="content">
    <div class="search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" placeholder="Buscar OS, cliente, placa…" oninput="filterOS(this.value)">
    </div>
    <div class="tab-row" id="osTabRow">
      <button class="tab-btn active" onclick="filterByStatus('todas',this)">Todas</button>
      <button class="tab-btn" onclick="filterByStatus('aberta',this)">Abertas</button>
      <button class="tab-btn" onclick="filterByStatus('andamento',this)">Andamento</button>
      <button class="tab-btn" onclick="filterByStatus('concluida',this)">Concluídas</button>
      <button class="tab-btn" onclick="filterByStatus('cancelada',this)">Canceladas</button>
    </div>
    <div id="osListContainer"><div class="loading-state"><i class="bi bi-hourglass-split"></i> Carregando…</div></div>
  </div>
</div>

<!-- ════ CLIENTES ════ -->
<div class="page" id="page-clientes">
  <div class="topnav">
    <div class="topnav-logo">OS</div>
    <div class="topnav-title flex-fill">Clientes</div>
    <button class="btn-icon accent" onclick="openNewCliente()"><i class="bi bi-plus-lg"></i></button>
  </div>
  <div class="content">
    <div class="search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" placeholder="Buscar cliente, CPF/CNPJ, telefone…" oninput="filterClientes(this.value)">
    </div>
    <div id="clienteListContainer"><div class="loading-state"><i class="bi bi-hourglass-split"></i> Carregando…</div></div>
  </div>
</div>

<!-- ════ USUÁRIOS ════ -->
<div class="page" id="page-usuarios">
  <div class="topnav">
    <div class="topnav-logo">OS</div>
    <div class="topnav-title flex-fill">Usuários</div>
    <button class="btn-icon accent" onclick="openNewUsuario()"><i class="bi bi-plus-lg"></i></button>
  </div>
  <div class="content">
    <div id="usuarioListContainer"><div class="loading-state"><i class="bi bi-hourglass-split"></i> Carregando…</div></div>
  </div>
</div>

<!-- ════ CONFIGURAÇÕES ════ -->
<div class="page" id="page-settings">
  <div class="topnav">
    <button class="btn-icon me-2" onclick="goBack()"><i class="bi bi-arrow-left"></i></button>
    <div class="topnav-title flex-fill">Configurações</div>
  </div>
  <div class="content">
    <div class="settings-section">
      <div class="settings-section-title">Aparência</div>
      <div class="card-custom d-flex align-items-center justify-content-between" style="padding:14px 16px">
        <div class="d-flex align-items-center gap-3">
          <div class="btn-icon" style="pointer-events:none"><i class="bi bi-moon-stars-fill" id="settingsThemeIcon"></i></div>
          <div>
            <div style="font-weight:600;font-size:14.5px" id="settingsThemeLabel">Modo Escuro</div>
            <div class="text-muted-custom">Alterna entre tema claro e escuro</div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-sun theme-icon" id="sunIcon"></i>
          <div class="theme-toggle" onclick="toggleTheme()" title="Alternar tema"></div>
          <i class="bi bi-moon-stars theme-icon" id="moonIcon"></i>
        </div>
      </div>
    </div>

    <div class="settings-section">
      <div class="settings-section-title">Dados da Empresa</div>
      <div class="mb-3">
        <label class="form-label">Nome / Razão Social</label>
        <input type="text" class="form-control" id="cfg-nome" value="<?= cfg('nome','Auto Center São Paulo') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">CNPJ</label>
        <input type="text" class="form-control" id="cfg-cnpj" value="<?= cfg('cnpj','') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Endereço</label>
        <input type="text" class="form-control" id="cfg-endereco" value="<?= cfg('endereco','') ?>">
      </div>
      <div class="row g-2 mb-3">
        <div class="col-6">
          <label class="form-label">Cidade</label>
          <input type="text" class="form-control" id="cfg-cidade" value="<?= cfg('cidade','') ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Estado</label>
          <input type="text" class="form-control" id="cfg-estado" value="<?= cfg('estado','') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Telefone / WhatsApp</label>
        <input type="text" class="form-control" id="cfg-tel" value="<?= cfg('telefone','') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">E-mail</label>
        <input type="email" class="form-control" id="cfg-email" value="<?= cfg('email','') ?>">
      </div>
    </div>

    <div class="settings-section">
      <div class="settings-section-title">Personalização</div>
      <div class="mb-3">
        <label class="form-label">Prefixo da OS</label>
        <input type="text" class="form-control" id="cfg-prefix" value="<?= cfg('prefixo_os','OS-') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Próximo número de OS</label>
        <input type="number" class="form-control" id="cfg-nextOs" value="<?= cfg('proximo_os','1') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Mensagem padrão (rodapé da OS)</label>
        <textarea class="form-control" rows="3" id="cfg-msg"><?= cfg('mensagem_os','') ?></textarea>
      </div>
    </div>

    <div class="settings-section">
      <div class="settings-section-title">Segurança</div>
      <div class="mb-3">
        <label class="form-label">Nova Senha</label>
        <input type="password" class="form-control" id="cfg-nova-senha" placeholder="Deixe em branco para não alterar">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirmar Nova Senha</label>
        <input type="password" class="form-control" id="cfg-conf-senha" placeholder="••••••••">
      </div>
    </div>

    <button class="btn-primary-custom mb-3" onclick="saveSettings()"><i class="bi bi-check-lg me-2"></i>Salvar Configurações</button>
    <button class="btn-danger-custom w-100" onclick="doLogout()"><i class="bi bi-box-arrow-right me-2"></i>Sair do sistema</button>
    <div style="height:32px"></div>
  </div>
</div>

<!-- ════ BOTTOM NAV ════ -->
<nav class="bottom-nav" id="bottomNav">
  <div class="nav-item active" id="nav-dashboard" onclick="goTab('dashboard')">
    <i class="bi bi-speedometer2"></i><span>Painel</span>
  </div>
  <div class="nav-item" id="nav-os" onclick="goTab('os')">
    <i class="bi bi-file-earmark-text"></i><span>OS</span>
  </div>
  <div class="nav-item" id="nav-clientes" onclick="goTab('clientes')">
    <i class="bi bi-people"></i><span>Clientes</span>
  </div>
  <div class="nav-item" id="nav-usuarios" onclick="goTab('usuarios')">
    <i class="bi bi-person-badge"></i><span>Usuários</span>
  </div>
</nav>

<!-- ════ MODAL OS ════ -->
<div class="modal-overlay" id="modalOS">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="fw-head" style="font-size:18px" id="modalOSNum">Nova OS</span>
      <button class="btn-icon" onclick="closeModal('modalOS')"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="d-flex gap-2 mb-3 flex-wrap" id="statusStrip">
      <button class="tab-btn active" onclick="setStatus('aberta',this)">🔵 Aberta</button>
      <button class="tab-btn" onclick="setStatus('andamento',this)">🟡 Andamento</button>
      <button class="tab-btn" onclick="setStatus('concluida',this)">🟢 Concluída</button>
      <button class="tab-btn" onclick="setStatus('cancelada',this)">🔴 Cancelada</button>
    </div>

    <div class="divider"></div>
    <div class="sec-title">Dados Gerais</div>
    <div class="row g-2 mb-3">
      <div class="col-6">
        <label class="form-label">Data Abertura</label>
        <input type="date" class="form-control" id="os-data">
      </div>
      <div class="col-6">
        <label class="form-label">Previsão Entrega</label>
        <input type="date" class="form-control" id="os-entrega">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Responsável (Mecânico)</label>
      <select class="form-select" id="os-responsavel">
        <option value="">— Selecione —</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Atendente</label>
      <select class="form-select" id="os-atendente">
        <option value="">— Selecione —</option>
      </select>
    </div>

    <div class="divider"></div>
    <div class="sec-title">Cliente &amp; Veículo</div>
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <select class="form-select" id="os-cliente">
        <option value="">— Selecione o cliente —</option>
      </select>
    </div>
    <div class="row g-2 mb-3">
      <div class="col-7">
        <label class="form-label">Veículo</label>
        <input type="text" class="form-control" id="os-veiculo" placeholder="Honda Civic 2019">
      </div>
      <div class="col-5">
        <label class="form-label">Placa</label>
        <input type="text" class="form-control" id="os-placa" placeholder="ABC-1D23">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">KM Atual</label>
      <input type="number" class="form-control" id="os-km" placeholder="0">
    </div>

    <div class="divider"></div>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="sec-title mb-0">Serviços / Peças</div>
      <button class="btn-icon" onclick="addServiceRow()"><i class="bi bi-plus"></i></button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 60px 80px 32px;gap:6px;font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:0 2px;margin-bottom:4px">
      <span>Descrição</span><span>Qtd</span><span>Unit. R$</span><span></span>
    </div>
    <div id="serviceRows"></div>

    <div class="divider"></div>
    <div class="total-row">
      <span class="text-muted-custom">Subtotal serviços</span>
      <span id="subtotalServ">R$ 0,00</span>
    </div>
    <div class="total-row">
      <span class="text-muted-custom">Subtotal peças</span>
      <span id="subtotalPecas">R$ 0,00</span>
    </div>
    <div class="total-row">
      <span class="text-muted-custom">Desconto</span>
      <div class="input-prefix-wrap" style="width:90px">
        <span class="prefix">R$</span>
        <input type="number" class="form-control" id="os-desconto" style="height:32px;font-size:13px" value="0" min="0" step="0.01" oninput="recalc()">
      </div>
    </div>
    <div class="total-row grand">
      <span>Total Geral</span>
      <span class="text-accent" id="totalGeral">R$ 0,00</span>
    </div>

    <div class="mb-3 mt-3">
      <label class="form-label">Observações / Descrição do problema</label>
      <textarea class="form-control" rows="3" id="os-obs" placeholder="Descreva o problema relatado pelo cliente…"></textarea>
    </div>

    <div class="d-flex gap-2 mt-2">
      <button class="btn-primary-custom" style="flex:1" onclick="saveOS()"><i class="bi bi-check-lg me-1"></i>Salvar OS</button>
      <button class="btn-icon btn-danger-custom" style="width:44px;height:44px;font-size:18px" onclick="closeModal('modalOS')"><i class="bi bi-x-lg"></i></button>
    </div>
  </div>
</div>

<!-- ════ MODAL CLIENTE ════ -->
<div class="modal-overlay" id="modalCliente">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="fw-head" style="font-size:18px" id="modalClienteTitle">Novo Cliente</span>
      <button class="btn-icon" onclick="closeModal('modalCliente')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sec-title">Dados Pessoais</div>
    <div class="mb-3">
      <label class="form-label">Nome Completo</label>
      <input type="text" class="form-control" id="cl-nome" placeholder="João da Silva">
    </div>
    <div class="row g-2 mb-3">
      <div class="col-6">
        <label class="form-label">CPF / CNPJ</label>
        <input type="text" class="form-control" id="cl-doc" placeholder="000.000.000-00">
      </div>
      <div class="col-6">
        <label class="form-label">Data Nasc.</label>
        <input type="date" class="form-control" id="cl-nasc">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Telefone / WhatsApp</label>
      <input type="tel" class="form-control" id="cl-tel" placeholder="(11) 9 0000-0000">
    </div>
    <div class="mb-3">
      <label class="form-label">E-mail</label>
      <input type="email" class="form-control" id="cl-email" placeholder="cliente@email.com">
    </div>
    <div class="mb-3">
      <label class="form-label">Endereço</label>
      <input type="text" class="form-control" id="cl-endereco" placeholder="Rua, número, bairro">
    </div>
    <div class="divider"></div>
    <div class="sec-title">Veículos Cadastrados</div>
    <div id="veiculoRows">
      <div class="service-row">
        <input type="text" class="form-control" placeholder="Modelo / Marca / Ano" data-campo="modelo">
        <input type="text" class="form-control qty" placeholder="Placa" data-campo="placa">
        <button class="btn-danger-custom" onclick="this.closest('.service-row').remove()"><i class="bi bi-trash"></i></button>
      </div>
    </div>
    <button class="btn-ghost w-100 mt-2" onclick="addVeiculoRow()"><i class="bi bi-plus me-1"></i>Adicionar Veículo</button>
    <div class="mb-3 mt-3">
      <label class="form-label">Observações</label>
      <textarea class="form-control" rows="2" id="cl-obs" placeholder="Informações adicionais…"></textarea>
    </div>
    <button class="btn-primary-custom" onclick="saveCliente()"><i class="bi bi-check-lg me-1"></i>Salvar Cliente</button>
  </div>
</div>

<!-- ════ MODAL USUÁRIO ════ -->
<div class="modal-overlay" id="modalUsuario">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="fw-head" style="font-size:18px" id="modalUsuarioTitle">Novo Usuário</span>
      <button class="btn-icon" onclick="closeModal('modalUsuario')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="mb-3">
      <label class="form-label">Nome Completo</label>
      <input type="text" class="form-control" id="us-nome" placeholder="Ex: João Mecânico">
    </div>
    <div class="mb-3">
      <label class="form-label">E-mail / Login</label>
      <input type="email" class="form-control" id="us-email" placeholder="joao@oficina.com.br">
    </div>
    <div class="mb-3">
      <label class="form-label">Perfil de Acesso</label>
      <select class="form-select" id="us-role">
        <option value="admin">Administrador</option>
        <option value="mecanico" selected>Mecânico</option>
        <option value="atendente">Atendente</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" class="form-control" id="us-senha" placeholder="Deixe em branco para não alterar (na edição)">
    </div>
    <div class="mb-3">
      <label class="form-label">Telefone</label>
      <input type="tel" class="form-control" id="us-tel" placeholder="(11) 9 0000-0000">
    </div>
    <div class="mb-3">
      <label class="form-label">Status</label>
      <select class="form-select" id="us-status">
        <option value="ativo">Ativo</option>
        <option value="inativo">Inativo</option>
      </select>
    </div>
    <button class="btn-primary-custom" onclick="saveUsuario()"><i class="bi bi-check-lg me-1"></i>Salvar Usuário</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ─── STATE ─── */
let osList = [], clienteList = [], usuarioList = [];
let serviceItems = [];
let currentOS     = null;
let currentStatus = 'aberta';
let _currentStatusFilter = 'todas';
let _currentOSBusca      = '';
let _editClienteId  = null;
let _editUsuarioId  = null;

/* ─── API HELPER ─── */
async function apiFetch(url, opts = {}) {
  try {
    const res = await fetch(url, opts);
    if (res.status === 401) { window.location.href = 'login.php'; return { ok: false }; }
    return await res.json();
  } catch {
    return { ok: false, erro: 'Erro de comunicação com o servidor.' };
  }
}

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ─── NAVIGATION ─── */
function goPage(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  const noNav = ['page-settings'];
  document.getElementById('bottomNav').style.display = noNav.includes(id) ? 'none' : 'flex';
}

function goTab(tab) {
  const map = { dashboard:'page-dashboard', os:'page-os', clientes:'page-clientes', usuarios:'page-usuarios' };
  goPage(map[tab]);
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('nav-' + tab);
  if (el) el.classList.add('active');
}

function goBack() { goTab('dashboard'); }

/* ─── THEME ─── */
let isLight = false;
function toggleTheme() {
  isLight = !isLight;
  document.documentElement.classList.toggle('light', isLight);
  localStorage.setItem('oficina-theme', isLight ? 'light' : 'dark');
  updateThemeUI();
}
function updateThemeUI() {
  const moonClass = 'bi bi-moon-stars-fill';
  const sunClass  = 'bi bi-sun-fill';
  const cls = isLight ? sunClass : moonClass;
  ['themeIconDash','settingsThemeIcon'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.className = cls;
  });
  const sLabel = document.getElementById('settingsThemeLabel');
  if (sLabel) sLabel.textContent = isLight ? 'Modo Claro' : 'Modo Escuro';
  const sun  = document.getElementById('sunIcon');
  const moon = document.getElementById('moonIcon');
  if (sun)  sun.style.color  = isLight ? 'var(--accent)' : 'var(--muted)';
  if (moon) moon.style.color = isLight ? 'var(--muted)'  : 'var(--accent)';
}

/* ─── LOGOUT ─── */
function doLogout() { window.location.href = 'logout.php'; }

/* ─── DASHBOARD STATS ─── */
async function carregarStats() {
  const data = await apiFetch('api/dashboard/stats.php');
  if (!data.ok) return;
  const el = id => document.getElementById(id);
  el('stat-abertas').textContent   = data.abertas;
  el('stat-andamento').textContent = data.andamento;
  el('stat-concluidas').textContent= data.concluidas;
  el('stat-receita').innerHTML = 'R$&nbsp;<span style="font-size:20px">' + escHtml(data.receita_mes) + '</span>';
}

/* ─── MAPAS DE STATUS ─── */
const statusClass = { aberta:'badge-aberta', andamento:'badge-andamento', concluida:'badge-concluida', cancelada:'badge-cancelada' };
const statusLabel = { aberta:'Aberta', andamento:'Em Andamento', concluida:'Concluída', cancelada:'Cancelada' };

function osCardHtml(o, onclick) {
  return `
    <div class="card-custom clickable" onclick="${onclick}">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <span class="fw-head" style="font-size:15px">OS #${escHtml(o.numero)}</span>
          <span class="badge-status ${statusClass[o.status]||''} ms-2">${statusLabel[o.status]||o.status}</span>
        </div>
        <span class="text-muted-custom" style="font-size:12px">${escHtml(o.data_abertura)}</span>
      </div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-person text-accent" style="font-size:14px"></i>
        <span style="font-size:13.5px">${escHtml(o.cliente_nome||'—')}</span>
      </div>
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-car-front text-accent" style="font-size:14px"></i>
        <span style="font-size:13.5px">${escHtml(o.veiculo_modelo||'—')} · ${escHtml(o.veiculo_placa||'—')}</span>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <span class="tag"><i class="bi bi-person-gear"></i> ${escHtml(o.responsavel_nome||'—')}</span>
        <span class="fw-head text-accent">R$ ${parseFloat(o.total||0).toLocaleString('pt-BR',{minimumFractionDigits:2})}</span>
      </div>
    </div>`;
}

/* ─── OS ─── */
async function carregarOS() {
  const data = await apiFetch('api/os/listar.php');
  if (data.ok) {
    osList = data.os;
    renderOS(_currentOSBusca, _currentStatusFilter);
    /* Atualiza resumo no dashboard (3 últimas) */
    const dash = document.getElementById('dashOsList');
    if (dash) {
      const recentes = osList.slice(0, 3);
      if (!recentes.length) {
        dash.innerHTML = '<div class="empty-state"><i class="bi bi-file-earmark-x"></i><p>Nenhuma OS cadastrada.</p></div>';
      } else {
        dash.innerHTML = recentes.map(o => osCardHtml(o, `openOS(${o.id})`)).join('');
      }
    }
  }
}

function renderOS(filter = '', statusFilter = 'todas') {
  const c = document.getElementById('osListContainer');
  const term = filter.toLowerCase();
  let items = osList.filter(o => {
    const matchText = !term ||
      (o.numero||'').toLowerCase().includes(term) ||
      (o.cliente_nome||'').toLowerCase().includes(term) ||
      (o.veiculo_placa||'').toLowerCase().includes(term) ||
      (o.veiculo_modelo||'').toLowerCase().includes(term);
    const matchStatus = statusFilter === 'todas' || o.status === statusFilter;
    return matchText && matchStatus;
  });

  if (!items.length) {
    c.innerHTML = '<div class="empty-state"><i class="bi bi-file-earmark-x"></i><p>Nenhuma OS encontrada.</p></div>';
    return;
  }
  c.innerHTML = items.map(o => osCardHtml(o, `openOS(${o.id})`)).join('');
}

function filterOS(v) { _currentOSBusca = v; renderOS(v, _currentStatusFilter); }
function filterByStatus(s, btn) {
  _currentStatusFilter = s;
  document.querySelectorAll('#osTabRow .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderOS(_currentOSBusca, s);
}

function populateClienteSelect() {
  const sel = document.getElementById('os-cliente');
  const val = sel.value;
  sel.innerHTML = '<option value="">— Selecione o cliente —</option>' +
    clienteList.map(c => `<option value="${c.id}">${escHtml(c.nome)}</option>`).join('');
  if (val) sel.value = val;
}

function populateUsuarioSelects() {
  const mecanicos = usuarioList.filter(u => ['admin','mecanico'].includes(u.perfil) && u.status === 'ativo');
  const todos     = usuarioList.filter(u => u.status === 'ativo');
  const valResp   = document.getElementById('os-responsavel').value;
  const valAten   = document.getElementById('os-atendente').value;

  document.getElementById('os-responsavel').innerHTML = '<option value="">— Responsável —</option>' +
    mecanicos.map(u => `<option value="${u.id}">${escHtml(u.nome)}</option>`).join('');
  document.getElementById('os-atendente').innerHTML = '<option value="">— Atendente —</option>' +
    todos.map(u => `<option value="${u.id}">${escHtml(u.nome)}</option>`).join('');

  if (valResp) document.getElementById('os-responsavel').value = valResp;
  if (valAten) document.getElementById('os-atendente').value   = valAten;
}

async function openOS(id) {
  openModal('modalOS');
  document.getElementById('modalOSNum').textContent = 'Carregando…';
  const data = await apiFetch('api/os/detalhe.php?id=' + id);
  if (!data.ok) { toast(data.erro || 'Erro ao carregar OS.', 'error'); closeModal('modalOS'); return; }
  const o = data.os;
  currentOS = o;

  document.getElementById('modalOSNum').textContent        = 'OS #' + escHtml(o.numero);
  document.getElementById('os-data').value                 = o.data_abertura || '';
  document.getElementById('os-entrega').value              = o.data_entrega  || '';
  document.getElementById('os-cliente').value              = o.cliente_id    || '';
  document.getElementById('os-responsavel').value          = o.responsavel_id|| '';
  document.getElementById('os-atendente').value            = o.atendente_id  || '';
  document.getElementById('os-veiculo').value              = o.veiculo_modelo|| '';
  document.getElementById('os-placa').value                = o.veiculo_placa || '';
  document.getElementById('os-km').value                   = o.km_atual      || '';
  document.getElementById('os-obs').value                  = o.obs           || '';
  document.getElementById('os-desconto').value             = o.desconto      || '0';

  currentStatus = o.status || 'aberta';
  const statusIdx = { aberta:0, andamento:1, concluida:2, cancelada:3 };
  document.querySelectorAll('#statusStrip .tab-btn').forEach((b, i) =>
    b.classList.toggle('active', i === statusIdx[currentStatus])
  );

  serviceItems = (o.itens || []).map(it => ({
    desc: it.descricao, tipo: it.tipo,
    qty:  parseFloat(it.quantidade),
    unit: parseFloat(it.valor_unitario)
  }));
  renderServiceRows();
}

function openNewOS() {
  currentOS     = null;
  currentStatus = 'aberta';
  serviceItems  = [{ desc:'', tipo:'servico', qty:1, unit:0 }];

  document.getElementById('modalOSNum').textContent     = 'Nova OS';
  document.getElementById('os-data').value              = new Date().toISOString().slice(0,10);
  document.getElementById('os-entrega').value           = '';
  document.getElementById('os-cliente').value           = '';
  document.getElementById('os-responsavel').value       = '';
  document.getElementById('os-atendente').value         = '';
  document.getElementById('os-veiculo').value           = '';
  document.getElementById('os-placa').value             = '';
  document.getElementById('os-km').value                = '';
  document.getElementById('os-obs').value               = '';
  document.getElementById('os-desconto').value          = '0';

  document.querySelectorAll('#statusStrip .tab-btn').forEach((b, i) => b.classList.toggle('active', i === 0));
  renderServiceRows();
  openModal('modalOS');
}

/* ─── SERVICE ROWS ─── */
function renderServiceRows() {
  const c = document.getElementById('serviceRows');
  c.innerHTML = serviceItems.map((s, i) => `
    <div class="service-row" id="srow-${i}">
      <input type="text" class="form-control" placeholder="Serviço ou peça" value="${escHtml(s.desc)}"
             oninput="serviceItems[${i}].desc=this.value">
      <input type="number" class="form-control qty" placeholder="Qtd" value="${s.qty}" min="1"
             oninput="serviceItems[${i}].qty=+this.value;recalc()">
      <input type="number" class="form-control price" placeholder="0,00" value="${s.unit}" min="0" step="0.01"
             oninput="serviceItems[${i}].unit=+this.value;recalc()">
      <button class="btn-danger-custom" style="width:32px;height:40px;padding:0;font-size:14px"
              onclick="removeServiceRow(${i})"><i class="bi bi-trash"></i></button>
    </div>`).join('');
  recalc();
}
function addServiceRow() { serviceItems.push({ desc:'', tipo:'servico', qty:1, unit:0 }); renderServiceRows(); }
function removeServiceRow(i) { serviceItems.splice(i,1); renderServiceRows(); }
function recalc() {
  const desc    = parseFloat(document.getElementById('os-desconto')?.value || 0);
  const totalBruto = serviceItems.reduce((acc, s) => acc + (s.qty||0)*(s.unit||0), 0);
  const total   = Math.max(0, totalBruto - desc);
  const serv    = serviceItems.filter(s => s.tipo==='servico').reduce((a,s) => a+s.qty*s.unit, 0);
  const peca    = serviceItems.filter(s => s.tipo==='peca').reduce((a,s) => a+s.qty*s.unit, 0);
  document.getElementById('totalGeral').textContent      = 'R$ ' + total.toLocaleString('pt-BR',{minimumFractionDigits:2});
  document.getElementById('subtotalServ').textContent    = 'R$ ' + serv.toLocaleString('pt-BR',{minimumFractionDigits:2});
  document.getElementById('subtotalPecas').textContent   = 'R$ ' + peca.toLocaleString('pt-BR',{minimumFractionDigits:2});
}
function setStatus(s, btn) {
  currentStatus = s;
  document.querySelectorAll('#statusStrip .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

async function saveOS() {
  if (!document.getElementById('os-data').value) { toast('Informe a data de abertura.', 'error'); return; }

  const dados = {
    id:             currentOS ? currentOS.id : null,
    status:         currentStatus,
    data_abertura:  document.getElementById('os-data').value,
    data_entrega:   document.getElementById('os-entrega').value  || null,
    cliente_id:     document.getElementById('os-cliente').value  || null,
    responsavel_id: document.getElementById('os-responsavel').value || null,
    atendente_id:   document.getElementById('os-atendente').value   || null,
    veiculo_modelo: document.getElementById('os-veiculo').value,
    veiculo_placa:  document.getElementById('os-placa').value,
    km_atual:       document.getElementById('os-km').value       || null,
    obs:            document.getElementById('os-obs').value,
    desconto:       parseFloat(document.getElementById('os-desconto').value || 0),
    itens: serviceItems.map(s => ({
      descricao: s.desc, tipo: s.tipo,
      quantidade: s.qty, valor_unitario: s.unit
    }))
  };

  const res = await apiFetch('api/os/salvar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });

  if (res.ok) {
    closeModal('modalOS');
    toast('OS salva com sucesso!', 'success');
    carregarOS();
    carregarStats();
  } else {
    toast(res.erro || 'Erro ao salvar OS.', 'error');
  }
}

/* ─── CLIENTES ─── */
async function carregarClientes() {
  const data = await apiFetch('api/clientes/listar.php');
  if (data.ok) {
    clienteList = data.clientes;
    renderClientes();
    populateClienteSelect();
  }
}

function renderClientes(filter = '') {
  const c = document.getElementById('clienteListContainer');
  const items = clienteList.filter(cl =>
    !filter ||
    (cl.nome||'').toLowerCase().includes(filter.toLowerCase()) ||
    (cl.cpf_cnpj||'').includes(filter) ||
    (cl.telefone||'').includes(filter)
  );
  if (!items.length) {
    c.innerHTML = '<div class="empty-state"><i class="bi bi-person-x"></i><p>Nenhum cliente encontrado.</p></div>';
    return;
  }
  c.innerHTML = items.map(cl => `
    <div class="card-custom clickable" onclick="editCliente(${cl.id})">
      <div class="d-flex align-items-center gap-3 mb-2">
        <div class="avatar">${escHtml((cl.nome||'?').charAt(0))}</div>
        <div class="flex-fill">
          <div class="fw-head" style="font-size:15px">${escHtml(cl.nome)}</div>
          <div class="text-muted-custom">${escHtml(cl.cpf_cnpj||'—')}</div>
        </div>
        <div class="btn-icon"><i class="bi bi-chevron-right"></i></div>
      </div>
      <div class="d-flex gap-3" style="font-size:13px;color:var(--muted)">
        <span><i class="bi bi-telephone me-1 text-accent"></i>${escHtml(cl.telefone||'—')}</span>
        <span><i class="bi bi-car-front me-1 text-accent"></i>${cl.veiculos&&cl.veiculos[0]?escHtml(cl.veiculos[0].modelo):'—'}</span>
        <span class="ms-auto"><i class="bi bi-file-earmark-text me-1 text-accent"></i>${cl.total_os||0} OS</span>
      </div>
    </div>`).join('');
}

function filterClientes(v) { renderClientes(v); }

function openNewCliente() {
  _editClienteId = null;
  document.getElementById('modalClienteTitle').textContent = 'Novo Cliente';
  ['cl-nome','cl-doc','cl-tel','cl-email','cl-endereco','cl-obs'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('cl-nasc').value = '';
  document.getElementById('veiculoRows').innerHTML = `
    <div class="service-row">
      <input type="text" class="form-control" placeholder="Modelo / Marca / Ano" data-campo="modelo">
      <input type="text" class="form-control qty" placeholder="Placa" data-campo="placa">
      <button class="btn-danger-custom" onclick="this.closest('.service-row').remove()"><i class="bi bi-trash"></i></button>
    </div>`;
  openModal('modalCliente');
}

function editCliente(id) {
  const cl = clienteList.find(c => c.id == id);
  if (!cl) return;
  _editClienteId = cl.id;
  document.getElementById('modalClienteTitle').textContent = 'Editar Cliente';
  document.getElementById('cl-nome').value     = cl.nome     || '';
  document.getElementById('cl-doc').value      = cl.cpf_cnpj || '';
  document.getElementById('cl-tel').value      = cl.telefone || '';
  document.getElementById('cl-email').value    = cl.email    || '';
  document.getElementById('cl-endereco').value = cl.endereco || '';
  document.getElementById('cl-nasc').value     = cl.data_nasc|| '';
  document.getElementById('cl-obs').value      = cl.obs      || '';

  const veicRows = document.getElementById('veiculoRows');
  if (cl.veiculos && cl.veiculos.length) {
    veicRows.innerHTML = cl.veiculos.map(v => `
      <div class="service-row" data-veiculo-id="${v.id}">
        <input type="text" class="form-control" value="${escHtml(v.modelo)}" placeholder="Modelo / Marca / Ano" data-campo="modelo">
        <input type="text" class="form-control qty" value="${escHtml(v.placa||'')}" placeholder="Placa" data-campo="placa">
        <button class="btn-danger-custom" onclick="this.closest('.service-row').remove()"><i class="bi bi-trash"></i></button>
      </div>`).join('');
  } else {
    veicRows.innerHTML = `
      <div class="service-row">
        <input type="text" class="form-control" placeholder="Modelo / Marca / Ano" data-campo="modelo">
        <input type="text" class="form-control qty" placeholder="Placa" data-campo="placa">
        <button class="btn-danger-custom" onclick="this.closest('.service-row').remove()"><i class="bi bi-trash"></i></button>
      </div>`;
  }
  openModal('modalCliente');
}

function addVeiculoRow() {
  const c = document.getElementById('veiculoRows');
  const div = document.createElement('div');
  div.className = 'service-row';
  div.innerHTML = `<input type="text" class="form-control" placeholder="Modelo / Marca / Ano" data-campo="modelo"><input type="text" class="form-control qty" placeholder="Placa" data-campo="placa"><button class="btn-danger-custom" onclick="this.closest('.service-row').remove()"><i class="bi bi-trash"></i></button>`;
  c.appendChild(div);
}

async function saveCliente() {
  const nome = document.getElementById('cl-nome').value.trim();
  if (!nome) { toast('Informe o nome do cliente.', 'error'); return; }

  const veiculos = Array.from(document.querySelectorAll('#veiculoRows .service-row')).map(row => ({
    id:     row.dataset.veiculoId || null,
    modelo: row.querySelector('[data-campo="modelo"]')?.value?.trim() || '',
    placa:  row.querySelector('[data-campo="placa"]')?.value?.trim()  || ''
  })).filter(v => v.modelo);

  const dados = {
    id:        _editClienteId,
    nome,
    cpf_cnpj:  document.getElementById('cl-doc').value,
    telefone:  document.getElementById('cl-tel').value,
    email:     document.getElementById('cl-email').value,
    endereco:  document.getElementById('cl-endereco').value,
    data_nasc: document.getElementById('cl-nasc').value || null,
    obs:       document.getElementById('cl-obs').value,
    veiculos
  };

  const res = await apiFetch('api/clientes/salvar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });

  if (res.ok) {
    closeModal('modalCliente');
    toast('Cliente salvo com sucesso!', 'success');
    carregarClientes();
  } else {
    toast(res.erro || 'Erro ao salvar cliente.', 'error');
  }
}

/* ─── USUÁRIOS ─── */
const roleLabel = { admin:'Administrador', mecanico:'Mecânico', atendente:'Atendente' };
const roleClass  = { admin:'role-admin', mecanico:'role-mecanico', atendente:'role-atendente' };

async function carregarUsuarios() {
  const data = await apiFetch('api/usuarios/listar.php');
  if (data.ok) {
    usuarioList = data.usuarios;
    renderUsuarios();
    populateUsuarioSelects();
  }
}

function renderUsuarios() {
  const c = document.getElementById('usuarioListContainer');
  c.innerHTML = `<div class="sec-title">Equipe (${usuarioList.length} usuário${usuarioList.length!==1?'s':''})</div>` +
    usuarioList.map(u => `
    <div class="card-custom clickable" onclick="editUsuario(${u.id})">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar">${escHtml((u.nome||'?').charAt(0))}</div>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-head" style="font-size:14.5px">${escHtml(u.nome)}</span>
            <span class="user-role-badge ${roleClass[u.perfil]||''}">${roleLabel[u.perfil]||u.perfil}</span>
          </div>
          <div class="text-muted-custom">${escHtml(u.email)}</div>
          <div class="text-muted-custom" style="font-size:12px">${escHtml(u.telefone||'')}</div>
        </div>
        <span class="badge-status ${u.status==='ativo'?'badge-concluida':'badge-cancelada'}" style="font-size:11px">${u.status}</span>
      </div>
    </div>`).join('');
}

function openNewUsuario() {
  _editUsuarioId = null;
  document.getElementById('modalUsuarioTitle').textContent = 'Novo Usuário';
  ['us-nome','us-email','us-tel','us-senha'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('us-role').value   = 'mecanico';
  document.getElementById('us-status').value = 'ativo';
  openModal('modalUsuario');
}

function editUsuario(id) {
  const u = usuarioList.find(us => us.id == id);
  if (!u) return;
  _editUsuarioId = u.id;
  document.getElementById('modalUsuarioTitle').textContent = 'Editar Usuário';
  document.getElementById('us-nome').value   = u.nome     || '';
  document.getElementById('us-email').value  = u.email    || '';
  document.getElementById('us-tel').value    = u.telefone || '';
  document.getElementById('us-senha').value  = '';
  document.getElementById('us-role').value   = u.perfil   || 'mecanico';
  document.getElementById('us-status').value = u.status   || 'ativo';
  openModal('modalUsuario');
}

async function saveUsuario() {
  const nome  = document.getElementById('us-nome').value.trim();
  const email = document.getElementById('us-email').value.trim();
  if (!nome || !email) { toast('Preencha nome e e-mail.', 'error'); return; }

  const dados = {
    id:       _editUsuarioId,
    nome, email,
    perfil:   document.getElementById('us-role').value,
    telefone: document.getElementById('us-tel').value,
    status:   document.getElementById('us-status').value,
    senha:    document.getElementById('us-senha').value || null
  };

  const res = await apiFetch('api/usuarios/salvar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });

  if (res.ok) {
    closeModal('modalUsuario');
    toast('Usuário salvo com sucesso!', 'success');
    carregarUsuarios();
  } else {
    toast(res.erro || 'Erro ao salvar usuário.', 'error');
  }
}

/* ─── SETTINGS ─── */
async function saveSettings() {
  const dados = {
    nome:       document.getElementById('cfg-nome').value,
    cnpj:       document.getElementById('cfg-cnpj').value,
    endereco:   document.getElementById('cfg-endereco').value,
    cidade:     document.getElementById('cfg-cidade').value,
    estado:     document.getElementById('cfg-estado').value,
    telefone:   document.getElementById('cfg-tel').value,
    email:      document.getElementById('cfg-email').value,
    prefixo:    document.getElementById('cfg-prefix').value,
    proximo_os: document.getElementById('cfg-nextOs').value,
    mensagem:   document.getElementById('cfg-msg').value,
  };

  const novaSenha = document.getElementById('cfg-nova-senha').value;
  const confSenha = document.getElementById('cfg-conf-senha').value;
  if (novaSenha) {
    if (novaSenha !== confSenha) { toast('As senhas não coincidem.', 'error'); return; }
    dados.senha = novaSenha;
    dados.usuario_id = <?= (int)$_SESSION['usuario_id'] ?>;
  }

  const res = await apiFetch('api/configuracoes/salvar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });
  toast(res.ok ? 'Configurações salvas!' : (res.erro || 'Erro ao salvar.'), res.ok ? 'success' : 'error');
}

/* ─── MODALS ─── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('active'); });
});

/* ─── TOAST ─── */
function toast(msg, type = 'success') {
  const wrap = document.getElementById('toastWrap');
  const div  = document.createElement('div');
  div.className = `toast-msg ${type}`;
  div.innerHTML = `<i class="bi ${type==='success'?'bi-check-circle-fill':'bi-exclamation-circle-fill'}" style="color:var(--${type==='success'?'success':'danger'})"></i>${escHtml(msg)}`;
  wrap.appendChild(div);
  setTimeout(() => div.remove(), 3200);
}

/* ─── INIT ─── */
(async function() {
  const saved = localStorage.getItem('oficina-theme');
  if (saved === 'light') { isLight = true; document.documentElement.classList.add('light'); }
  updateThemeUI();
  await Promise.all([carregarStats(), carregarOS(), carregarClientes(), carregarUsuarios()]);
})();
</script>
</body>
</html>
