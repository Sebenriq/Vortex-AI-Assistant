<?php
// ============================================================
// pacientes/index.php  —  Lista y búsqueda de pacientes
// ============================================================

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Pacientes — Vortex AI';

// Búsqueda
$busqueda = trim($_GET['q'] ?? '');

if ($busqueda !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM pacientes
         WHERE nombre LIKE ? OR id = ?
         ORDER BY fecha_registro DESC"
    );
    $stmt->execute(['%' . $busqueda . '%', is_numeric($busqueda) ? (int)$busqueda : 0]);
} else {
    $stmt = $pdo->query('SELECT * FROM pacientes ORDER BY fecha_registro DESC');
}

$pacientes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Pacientes</h4>
  <a href="crear.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nuevo paciente
  </a>
</div>

<!-- Buscador -->
<form method="GET" class="mb-4">
  <div class="input-group" style="max-width:440px">
    <span class="input-group-text"><i class="bi bi-search"></i></span>
    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o ID…"
           value="<?= htmlspecialchars($busqueda) ?>">
    <button class="btn btn-outline-primary" type="submit">Buscar</button>
    <?php if ($busqueda): ?>
      <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
    <?php endif; ?>
  </div>
</form>

<?php if ($busqueda && empty($pacientes)): ?>
  <div class="alert alert-info">No se encontraron pacientes para "<strong><?= htmlspecialchars($busqueda) ?></strong>".</div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th><th>Nombre</th><th>Edad</th><th>Género</th>
            <th>Alergias</th><th>Fecha registro</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pacientes as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= $p['edad'] ?></td>
            <td><?= htmlspecialchars($p['genero']) ?></td>
            <td class="text-truncate" style="max-width:150px"><?= htmlspecialchars($p['alergias'] ?: '—') ?></td>
            <td class="text-muted small"><?= date('d/m/Y', strtotime($p['fecha_registro'])) ?></td>
            <td>
              <a href="ver.php?id=<?= $p['id'] ?>" class="btn btn-outline-info btn-sm" title="Ver historial">
                <i class="bi bi-eye"></i>
              </a>
              <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-outline-danger btn-sm"
                 title="Eliminar"
                 onclick="return confirm('¿Seguro que deseas eliminar a <?= addslashes(htmlspecialchars($p['nombre'])) ?>? Se eliminarán también sus consultas.')">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($pacientes)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              No hay pacientes registrados. <a href="crear.php">Registra el primero</a>.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
