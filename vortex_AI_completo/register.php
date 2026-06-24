<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$data = ['nombre' => '', 'username' => '', 'rol' => 'medico'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'rol' => trim($_POST['rol'] ?? 'medico'),
    ];
    $password = (string)($_POST['password'] ?? '');

    if ($data['nombre'] === '') $errors[] = 'El nombre es obligatorio.';
    if ($data['username'] === '') $errors[] = 'El usuario es obligatorio.';
    if ($password === '') $errors[] = 'La contraseña es obligatoria.';
    elseif (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';

    if (!$errors) {
        $exists = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? LIMIT 1');
        $exists->execute([$data['username']]);

        if ($exists->fetch()) {
            $errors[] = 'Ese usuario ya existe.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, username, password, rol) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $data['nombre'],
                $data['username'],
                password_hash($password, PASSWORD_DEFAULT),
                $data['rol'],
            ]);
            flash('success', 'Usuario registrado correctamente.');
            redirect('login.php');
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro — Vortex AI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#07363b,#08747d)">
  <main class="card shadow-lg border-0" style="width:min(470px, calc(100% - 32px)); border-radius:16px">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-1">Crear usuario</h1>
      <p class="text-muted mb-4">Registra una cuenta para operar el sistema.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label fw-semibold">Nombre</label>
          <input name="nombre" class="form-control" value="<?= e($data['nombre']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Usuario</label>
          <input name="username" class="form-control" value="<?= e($data['username']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Contraseña</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Rol</label>
          <select name="rol" class="form-select">
            <option value="medico" <?= $data['rol'] === 'medico' ? 'selected' : '' ?>>Médico</option>
            <option value="enfermeria" <?= $data['rol'] === 'enfermeria' ? 'selected' : '' ?>>Enfermería</option>
            <option value="admin" <?= $data['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
          </select>
        </div>
        <button class="btn btn-primary w-100" type="submit">Crear cuenta</button>
      </form>
      <div class="mt-3 text-center">
        <a href="login.php">Volver al login</a>
      </div>
    </div>
  </main>
</body>
</html>
