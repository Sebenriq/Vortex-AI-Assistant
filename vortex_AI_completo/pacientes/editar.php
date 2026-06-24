<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) redirect('index.php');

$stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = ?');
$stmt->execute([$id]);
$paciente = $stmt->fetch();
if (!$paciente) redirect('index.php');

$pageTitle = 'Editar paciente';
$errors = [];
$datos = [
    'nombre' => (string)$paciente['nombre'],
    'edad' => (string)$paciente['edad'],
    'genero' => (string)$paciente['genero'],
    'alergias' => (string)($paciente['alergias'] ?? ''),
    'antecedentes' => (string)($paciente['antecedentes'] ?? ''),
    'descriptor_facial' => (string)($paciente['descriptor_facial'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'edad' => trim($_POST['edad'] ?? ''),
        'genero' => trim($_POST['genero'] ?? ''),
        'alergias' => trim($_POST['alergias'] ?? ''),
        'antecedentes' => trim($_POST['antecedentes'] ?? ''),
        'descriptor_facial' => trim($_POST['descriptor_facial'] ?? ''),
    ];

    $errors = validatePaciente($datos);

    if (!$errors) {
        $upd = $pdo->prepare(
            'UPDATE pacientes
             SET nombre = ?, edad = ?, genero = ?, alergias = ?, antecedentes = ?, descriptor_facial = ?
             WHERE id = ?'
        );
        $upd->execute([
            $datos['nombre'],
            (int)$datos['edad'],
            $datos['genero'],
            $datos['alergias'] ?: null,
            $datos['antecedentes'] ?: null,
            $datos['descriptor_facial'] ?: null,
            $id,
        ]);

        flash('success', 'Paciente actualizado correctamente.');
        redirect('index.php');
    }
}

require_once __DIR__ . '/../includes/layout.php';
?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Editar paciente #<?= (int)$id ?></h4>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $error): ?>
      <div><i class="bi bi-x-circle me-1"></i><?= e($error) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width:760px">
  <div class="card-body">
    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control" value="<?= e($datos['nombre']) ?>" required>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Edad <span class="text-danger">*</span></label>
          <input type="number" name="edad" class="form-control" min="1" max="119" value="<?= e($datos['edad']) ?>" required>
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Género <span class="text-danger">*</span></label>
          <select name="genero" class="form-select" required>
            <option value="">— Seleccionar —</option>
            <?php foreach (['Masculino','Femenino','Otro'] as $genero): ?>
              <option value="<?= $genero ?>" <?= $datos['genero'] === $genero ? 'selected' : '' ?>><?= $genero ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Alergias conocidas</label>
        <input type="text" name="alergias" class="form-control" value="<?= e($datos['alergias']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Antecedentes médicos</label>
        <textarea name="antecedentes" class="form-control" rows="3"><?= e($datos['antecedentes']) ?></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Descriptor facial</label>
        <textarea name="descriptor_facial" class="form-control" rows="2" placeholder="Reservado para futura integración por webcam"><?= e($datos['descriptor_facial']) ?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Guardar cambios</button>
        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
