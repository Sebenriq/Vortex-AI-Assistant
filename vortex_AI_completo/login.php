<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === '') $errors[] = 'El usuario es obligatorio.';
    if ($password === '') $errors[] = 'La contraseña es obligatoria.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        $valid = false;
        if ($user) {
            $valid = password_verify($password, $user['password']) || hash_equals((string)$user['password'], $password);
            if ($valid && !password_verify($password, $user['password'])) {
                $upd = $pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
                $upd->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
        }

        if ($valid) {
            loginUser($user);
            flash('success', 'Inicio de sesión correcto.');
            redirect('index.php');
        }

        $errors[] = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Vortex AI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#07363b,#08747d)">
  <main class="card shadow-lg border-0" style="width:min(430px, calc(100% - 32px)); border-radius:16px">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-1">Vortex AI Assistant</h1>
      <p class="text-muted mb-4">Acceso para personal de urgencias.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label fw-semibold">Usuario</label>
          <input name="username" class="form-control" value="<?= e($username) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
      </form>
      <div class="mt-3 text-center">
        <a href="register.php">Crear usuario</a>
      </div>
    </div>
  </main>
</body>
</html>
