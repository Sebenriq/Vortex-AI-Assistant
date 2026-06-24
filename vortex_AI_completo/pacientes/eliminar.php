<?php
// ============================================================
// pacientes/eliminar.php
// ============================================================

require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('DELETE FROM pacientes WHERE id = ?');
if ($stmt->execute([$id])) {
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Paciente eliminado correctamente.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Error al eliminar el paciente.'];
}

header('Location: index.php');
exit;
