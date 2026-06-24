<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
requireLogin();

$pageTitle = 'Nueva consulta';
$errors = [];
$datos = [
    'paciente_id' => (string)(filter_input(INPUT_GET, 'paciente_id', FILTER_VALIDATE_INT) ?: ''),
    'sintomas' => '',
    'temperatura' => '',
    'frecuencia_cardiaca' => '',
    'presion_arterial' => '',
    'saturacion_oxigeno' => '',
    'nivel_dolor' => '',
    'observaciones' => '',
];

$pacientes = $pdo->query('SELECT id, nombre FROM pacientes ORDER BY nombre ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'paciente_id' => trim($_POST['paciente_id'] ?? ''),
        'sintomas' => trim($_POST['sintomas'] ?? ''),
        'temperatura' => trim($_POST['temperatura'] ?? ''),
        'frecuencia_cardiaca' => trim($_POST['frecuencia_cardiaca'] ?? ''),
        'presion_arterial' => trim($_POST['presion_arterial'] ?? ''),
        'saturacion_oxigeno' => trim($_POST['saturacion_oxigeno'] ?? ''),
        'nivel_dolor' => trim($_POST['nivel_dolor'] ?? ''),
        'observaciones' => trim($_POST['observaciones'] ?? ''),
    ];

    $errors = validateConsulta($datos);

    if (!$errors) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO consultas
             (paciente_id, sintomas, temperatura, frecuencia_cardiaca, presion_arterial, saturacion_oxigeno, nivel_dolor, observaciones)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int)$datos['paciente_id'],
            $datos['sintomas'],
            (float)$datos['temperatura'],
            (int)$datos['frecuencia_cardiaca'],
            $datos['presion_arterial'] ?: null,
            (int)$datos['saturacion_oxigeno'],
            (int)$datos['nivel_dolor'],
            $datos['observaciones'] ?: null,
        ]);

        $consultaId = (int)$pdo->lastInsertId();
        $triage = calcularTriage($datos);
        $triageStmt = $pdo->prepare(
            'INSERT INTO triage (consulta_id, nivel_urgencia, confianza, recomendacion)
             VALUES (?, ?, ?, ?)'
        );
        $triageStmt->execute([$consultaId, $triage['nivel_urgencia'], $triage['confianza'], $triage['recomendacion']]);
        $pdo->commit();

        flash('success', 'Consulta registrada correctamente.');
        redirect('index.php');
    }
}

require_once __DIR__ . '/../includes/layout.php';
?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Registrar consulta</h4>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $error): ?>
      <div><i class="bi bi-x-circle me-1"></i><?= e($error) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width:900px">
  <div class="card-body">
    <form method="POST" novalidate>
      <?php require __DIR__ . '/form.php'; ?>
      <div class="d-flex gap-2">
        <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i>Guardar consulta</button>
        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
