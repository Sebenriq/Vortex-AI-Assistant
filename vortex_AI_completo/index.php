<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';

$totalPacientes = (int)$pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn();
$totalConsultas = (int)$pdo->query('SELECT COUNT(*) FROM consultas')->fetchColumn();
$casosCriticos = (int)$pdo->query("SELECT COUNT(*) FROM triage WHERE nivel_urgencia IN ('Crítica', 'Critica', 'Crítico', 'Critico')")->fetchColumn();
$casosAltos = (int)$pdo->query("SELECT COUNT(*) FROM triage WHERE nivel_urgencia IN ('Alta', 'Alto')")->fetchColumn();

$stmt = $pdo->query(
    'SELECT id, nombre, edad, genero, fecha_registro
     FROM pacientes
     ORDER BY fecha_registro DESC
     LIMIT 5'
);
$recientes = $stmt->fetchAll();

require_once __DIR__ . '/includes/layout.php';
?>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card metric-card">
      <div class="card-body">
        <div class="text-muted small">Total de pacientes</div>
        <div class="display-6 fw-bold"><?= $totalPacientes ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card metric-card">
      <div class="card-body">
        <div class="text-muted small">Total de consultas</div>
        <div class="display-6 fw-bold"><?= $totalConsultas ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card metric-card">
      <div class="card-body">
        <div class="text-muted small">Casos críticos</div>
        <div class="display-6 fw-bold text-danger"><?= $casosCriticos ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card metric-card">
      <div class="card-body">
        <div class="text-muted small">Casos altos</div>
        <div class="display-6 fw-bold text-warning"><?= $casosAltos ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person-lines-fill me-2 text-primary"></i>Pacientes registrados recientemente
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr><th>ID</th><th>Nombre</th><th>Edad</th><th>Género</th><th>Registro</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recientes as $p): ?>
                <tr>
                  <td>#<?= (int)$p['id'] ?></td>
                  <td><a class="fw-semibold" href="pacientes/ver.php?id=<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?></a></td>
                  <td><?= (int)$p['edad'] ?></td>
                  <td><?= e($p['genero']) ?></td>
                  <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$recientes): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay pacientes registrados.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold">Acciones rápidas</h5>
        <div class="d-grid gap-2 mt-3">
          <a class="btn btn-primary" href="pacientes/crear.php"><i class="bi bi-person-plus me-1"></i>Registrar paciente</a>
          <a class="btn btn-outline-primary" href="consultas/crear.php"><i class="bi bi-clipboard-plus me-1"></i>Registrar consulta</a>
          <a class="btn btn-outline-secondary" href="historial.php"><i class="bi bi-search me-1"></i>Buscar historial</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
