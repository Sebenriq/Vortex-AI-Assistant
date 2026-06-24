<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) redirect('index.php');

$stmt = $pdo->prepare('DELETE FROM consultas WHERE id = ?');
$stmt->execute([$id]);
flash('success', 'Consulta eliminada correctamente.');

$redirect = $_GET['redirect'] ?? '';
$pacienteId = filter_input(INPUT_GET, 'paciente_id', FILTER_VALIDATE_INT);

if ($redirect === 'paciente' && $pacienteId) {
    redirect('../pacientes/ver.php?id=' . $pacienteId);
}

redirect('index.php');
