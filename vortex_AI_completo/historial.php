<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Historial clínico';
$busqueda = trim($_GET['q'] ?? '');
$pacientes = [];

if ($busqueda !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, nombre, edad, genero, alergias, antecedentes, fecha_registro
         FROM pacientes
         WHERE nombre LIKE ? OR id = ?
         ORDER BY fecha_registro DESC'
    );
    $stmt->execute(['%' . $busqueda . '%', is_numeric($busqueda) ? (int)$busqueda : 0]);
    $pacientes = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/layout.php';
?>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-search me-2 text-primary"></i>Buscar paciente</h5>
    <form method="GET" class="row g-2">
      <div class="col-md-8">
        <input name="q" class="form-control" placeholder="Nombre o ID del paciente" value="<?= e($busqueda) ?>">
      </div>
      <div class="col-md-auto">
        <button class="btn btn-primary" type="submit">Buscar</button>
      </div>
    </form>
  </div>
</div>

<?php if ($busqueda === ''): ?>
  <div class="alert alert-info">Ingresa un nombre o ID para consultar el historial clínico.</div>
<?php elseif (!$pacientes): ?>
  <div class="alert alert-warning">No se encontraron pacientes para "<?= e($busqueda) ?>".</div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Resultados</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr><th>ID</th><th>Paciente</th><th>Edad</th><th>Género</th><th>Registro</th><th>Acción</th></tr>
          </thead>
          <tbody>
            <?php foreach ($pacientes as $p): ?>
              <tr>
                <td>#<?= (int)$p['id'] ?></td>
                <td class="fw-semibold"><?= e($p['nombre']) ?></td>
                <td><?= (int)$p['edad'] ?></td>
                <td><?= e($p['genero']) ?></td>
                <td class="text-muted small"><?= date('d/m/Y', strtotime($p['fecha_registro'])) ?></td>
                <td><a class="btn btn-outline-primary btn-sm" href="pacientes/ver.php?id=<?= (int)$p['id'] ?>">Ver historial</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
