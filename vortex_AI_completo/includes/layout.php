<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Vortex AI Assistant';
$user = currentUser();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$rootPrefix = preg_match('#/(pacientes|consultas)/#', $script) ? '../' : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $rootPrefix ?>styles.css">
  <style>
    body { background: #f4f8f7; }
    .app-shell { min-height: 100vh; display: grid; grid-template-columns: 260px minmax(0, 1fr); }
    .app-sidebar { background: #07363b; color: #fff; padding: 24px 16px; }
    .app-sidebar .brand { height: auto; padding: 0 10px 24px; border-bottom: 1px solid rgba(255,255,255,.12); }
    .app-sidebar .nav-link { color: #abc6c6; border-radius: 10px; padding: 12px 14px; }
    .app-sidebar .nav-link:hover, .app-sidebar .nav-link.active { color: #fff; background: rgba(25,167,167,.18); }
    .app-main { padding: 28px; }
    .top-strip { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; }
    .metric-card { border: 0; border-radius: 14px; box-shadow: 0 14px 34px rgba(21,67,70,.08); }
    @media (max-width: 900px) { .app-shell { grid-template-columns: 1fr; } .app-main { padding: 18px; } }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="app-sidebar">
    <div class="brand">
      <div class="brand-logo">VX</div>
      <div><strong>VORTEX</strong><small>AI ASSISTANT</small></div>
    </div>
    <nav class="nav flex-column gap-1 mt-4">
      <a class="nav-link <?= basename($script) === 'index.php' && strpos($script, '/pacientes/') === false && strpos($script, '/consultas/') === false ? 'active' : '' ?>" href="<?= $rootPrefix ?>index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link <?= strpos($script, '/pacientes/') !== false ? 'active' : '' ?>" href="<?= $rootPrefix ?>pacientes/index.php"><i class="bi bi-people me-2"></i>Pacientes</a>
      <a class="nav-link <?= strpos($script, '/consultas/') !== false ? 'active' : '' ?>" href="<?= $rootPrefix ?>consultas/index.php"><i class="bi bi-clipboard2-pulse me-2"></i>Consultas</a>
      <a class="nav-link" href="<?= $rootPrefix ?>historial.php"><i class="bi bi-journal-medical me-2"></i>Historial clínico</a>
      <a class="nav-link" href="<?= $rootPrefix ?>logout.php"><i class="bi bi-box-arrow-left me-2"></i>Cerrar sesión</a>
    </nav>
  </aside>
  <main class="app-main">
    <div class="top-strip">
      <div>
        <div class="text-primary small fw-bold text-uppercase">Centro de control</div>
        <h2 class="fw-bold mb-0"><?= e($pageTitle) ?></h2>
      </div>
      <?php if ($user): ?>
      <div class="badge text-bg-light border p-2">
        <i class="bi bi-person-circle me-1"></i><?= e($user['nombre']) ?> · <?= e($user['rol']) ?>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($flash): ?>
      <?php $class = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'danger'); ?>
      <div class="alert alert-<?= $class ?> alert-dismissible fade show" role="alert">
        <?= e($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>
