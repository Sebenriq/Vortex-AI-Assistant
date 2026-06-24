<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Consultas médicas';
$pacienteId = filter_input(INPUT_GET, 'paciente_id', FILTER_VALIDATE_INT);

$sql = 'SELECT c.*, p.nombre AS paciente_nombre, t.nivel_urgencia, t.confianza
        FROM consultas c
        INNER JOIN pacientes p ON p.id = c.paciente_id
        LEFT JOIN triage t ON t.consulta_id = c.id';

if ($pacienteId) {
    $stmt = $pdo->prepare($sql . ' WHERE c.paciente_id = ? ORDER BY c.fecha_consulta DESC');
    $stmt->execute([$pacienteId]);
} else {
    $stmt = $pdo->query($sql . ' ORDER BY c.fecha_consulta DESC');
}

$consultas = $stmt->fetchAll();
$badgeColors = ['Crítica' => 'danger', 'Alta' => 'warning text-dark', 'Media' => 'info text-dark', 'Baja' => 'success'];

require_once __DIR__ . '/../includes/layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Consultas médicas</h4>
  <a href="crear.php<?= $pacienteId ? '?paciente_id=' . $pacienteId : '' ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nueva consulta
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th><th>Paciente</th><th>Fecha</th><th>Síntomas</th><th>Triage</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($consultas as $c): ?>
          <tr>
            <td>#<?= (int)$c['id'] ?></td>
            <td><a class="fw-semibold" href="../pacientes/ver.php?id=<?= (int)$c['paciente_id'] ?>"><?= e($c['paciente_nombre']) ?></a></td>
            <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($c['fecha_consulta'])) ?></td>
            <td class="text-truncate" style="max-width:260px"><?= e($c['sintomas']) ?></td>
            <td>
              <?php if ($c['nivel_urgencia']): ?>
                <span class="badge bg-<?= $badgeColors[$c['nivel_urgencia']] ?? 'secondary' ?>"><?= e($c['nivel_urgencia']) ?></span>
              <?php else: ?>
                <span class="badge bg-secondary">Sin triage</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="editar.php?id=<?= (int)$c['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i></a>
              <a href="eliminar.php?id=<?= (int)$c['id'] ?>" class="btn btn-outline-danger btn-sm"
                 onclick="return confirm('¿Eliminar esta consulta?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$consultas): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No hay consultas registradas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
