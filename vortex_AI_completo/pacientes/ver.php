<?php
// ============================================================
// pacientes/ver.php  —  Historial clínico del paciente
// ============================================================

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = ?');
$stmt->execute([$id]);
$paciente = $stmt->fetch();
if (!$paciente) { header('Location: index.php'); exit; }

// Historial de consultas con triage
$historial = $pdo->prepare(
    'SELECT c.*, t.nivel_urgencia, t.confianza, t.recomendacion, t.fecha AS fecha_triage
     FROM consultas c
     LEFT JOIN triage t ON t.consulta_id = c.id
     WHERE c.paciente_id = ?
     ORDER BY c.fecha_consulta DESC'
);
$historial->execute([$id]);
$consultas = $historial->fetchAll();

$pageTitle = htmlspecialchars($paciente['nombre']) . ' — Historial Clínico';
$badgeColors = ['Crítica' => 'danger', 'Alta' => 'warning text-dark', 'Media' => 'info text-dark', 'Baja' => 'success'];

require_once __DIR__ . '/../includes/layout.php';
?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Historial Clínico</h4>
  <div class="ms-auto d-flex gap-2">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
    <a href="../consultas/crear.php?paciente_id=<?= $id ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nueva consulta</a>
  </div>
</div>

<!-- Ficha del paciente -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-primary text-white fw-semibold">
    <i class="bi bi-person-badge me-2"></i>Datos del paciente
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-sm-6 col-md-3">
        <div class="text-muted small">Nombre</div>
        <div class="fw-semibold"><?= htmlspecialchars($paciente['nombre']) ?></div>
      </div>
      <div class="col-sm-6 col-md-2">
        <div class="text-muted small">Edad</div>
        <div class="fw-semibold"><?= $paciente['edad'] ?> años</div>
      </div>
      <div class="col-sm-6 col-md-2">
        <div class="text-muted small">Género</div>
        <div class="fw-semibold"><?= htmlspecialchars($paciente['genero']) ?></div>
      </div>
      <div class="col-sm-6 col-md-2">
        <div class="text-muted small">ID</div>
        <div class="fw-semibold">#<?= $paciente['id'] ?></div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="text-muted small">Fecha de registro</div>
        <div class="fw-semibold"><?= date('d/m/Y', strtotime($paciente['fecha_registro'])) ?></div>
      </div>
      <?php if ($paciente['alergias']): ?>
      <div class="col-sm-6">
        <div class="text-muted small">Alergias</div>
        <div class="fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($paciente['alergias']) ?></div>
      </div>
      <?php endif; ?>
      <?php if ($paciente['antecedentes']): ?>
      <div class="col-12">
        <div class="text-muted small">Antecedentes</div>
        <div><?= nl2br(htmlspecialchars($paciente['antecedentes'])) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Historial de consultas -->
<h5 class="fw-bold mb-3"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Consultas (<?= count($consultas) ?>)</h5>

<?php if (empty($consultas)): ?>
  <div class="alert alert-info">Este paciente no tiene consultas registradas. <a href="../consultas/crear.php?paciente_id=<?= $id ?>">Registrar primera consulta</a>.</div>
<?php else: ?>

  <?php foreach ($consultas as $c): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
      <span class="fw-semibold"><i class="bi bi-calendar2-event me-2"></i><?= date('d/m/Y H:i', strtotime($c['fecha_consulta'])) ?></span>
      <div class="d-flex align-items-center gap-2">
        <?php if ($c['nivel_urgencia']): ?>
          <span class="badge bg-<?= $badgeColors[$c['nivel_urgencia']] ?? 'secondary' ?> fs-6"><?= $c['nivel_urgencia'] ?></span>
        <?php else: ?>
          <span class="badge bg-secondary">Sin triage</span>
        <?php endif; ?>
        <a href="../consultas/editar.php?id=<?= $c['id'] ?>" class="btn btn-outline-warning btn-sm py-0"><i class="bi bi-pencil"></i></a>
        <a href="../consultas/eliminar.php?id=<?= $c['id'] ?>&redirect=paciente&paciente_id=<?= $id ?>"
           class="btn btn-outline-danger btn-sm py-0"
           onclick="return confirm('¿Eliminar esta consulta?')"><i class="bi bi-trash"></i></a>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12">
          <div class="text-muted small">Síntomas</div>
          <div><?= nl2br(htmlspecialchars($c['sintomas'])) ?></div>
        </div>
        <!-- Signos vitales -->
        <?php
        $signos = [
            ['label'=>'Temperatura',          'value'=>$c['temperatura']          ? $c['temperatura'].'°C'    : null, 'icon'=>'thermometer-half'],
            ['label'=>'Frec. cardíaca',        'value'=>$c['frecuencia_cardiaca']  ? $c['frecuencia_cardiaca'].' lpm' : null, 'icon'=>'heart-pulse'],
            ['label'=>'Presión arterial',      'value'=>$c['presion_arterial']     ?: null, 'icon'=>'activity'],
            ['label'=>'Saturación O₂',         'value'=>$c['saturacion_oxigeno']!==null ? $c['saturacion_oxigeno'].'%'  : null, 'icon'=>'lungs'],
            ['label'=>'Nivel de dolor',        'value'=>$c['nivel_dolor']          ? $c['nivel_dolor'].'/10'    : null, 'icon'=>'emoji-frown'],
        ];
        foreach ($signos as $s):
          if ($s['value'] === null) continue;
        ?>
        <div class="col-6 col-md-4 col-lg-2">
          <div class="text-muted small"><i class="bi bi-<?= $s['icon'] ?> me-1"></i><?= $s['label'] ?></div>
          <div class="fw-semibold"><?= htmlspecialchars($s['value']) ?></div>
        </div>
        <?php endforeach; ?>

        <?php if ($c['observaciones']): ?>
        <div class="col-12">
          <div class="text-muted small">Observaciones</div>
          <div><?= nl2br(htmlspecialchars($c['observaciones'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($c['recomendacion']): ?>
        <div class="col-12">
          <div class="text-muted small">Recomendación de triage</div>
          <div class="alert alert-<?= $badgeColors[$c['nivel_urgencia']] ?? 'secondary' ?> py-2 mb-0">
            <?= htmlspecialchars($c['recomendacion']) ?>
            <?php if ($c['confianza']): ?>
              <span class="ms-2 badge bg-secondary"><?= $c['confianza'] ?>% confianza</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
