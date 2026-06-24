<?php
declare(strict_types=1);

function currentUser(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('#/(pacientes|consultas)/#', $script)) {
            $base = preg_replace('#/(pacientes|consultas)/.*$#', '', $script);
        } else {
            $base = preg_replace('#/[^/]+$#', '', $script);
        }
        redirect(rtrim((string)$base, '/') . '/login.php');
    }
}

function loginUser(array $user): void
{
    $_SESSION['usuario'] = [
        'id' => (int)$user['id'],
        'nombre' => $user['nombre'],
        'username' => $user['username'],
        'rol' => $user['rol'],
    ];
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
