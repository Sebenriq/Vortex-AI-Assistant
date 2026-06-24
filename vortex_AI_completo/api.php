<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';

header('Content-Type: application/json; charset=utf-8');

function apiResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiInput(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode((string)$raw, true);
    return is_array($json) ? $json : $_POST;
}

function routeParts(): array
{
    $resource = $_GET['resource'] ?? null;
    $id = isset($_GET['id']) && $_GET['id'] !== '' ? (int)$_GET['id'] : null;
    $action = $_GET['action'] ?? null;

    if ($resource) {
        return [$resource, $id, $action];
    }

    $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($scriptDir !== '' && substr($path, 0, strlen($scriptDir)) === $scriptDir) {
        $path = trim(substr($path, strlen($scriptDir)), '/');
    }
    $segments = array_values(array_filter(explode('/', $path)));
    if (($segments[0] ?? '') === 'api.php') array_shift($segments);

    if (($segments[0] ?? '') === 'auth') {
        return ['auth', null, $segments[1] ?? null];
    }

    return [$segments[0] ?? '', isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null, null];
}

[$resource, $id, $action] = routeParts();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($resource === 'auth') {
        handleAuthApi($pdo, $method, $action);
    }

    if (!isLoggedIn()) {
        apiResponse(['ok' => false, 'message' => 'Sesión no iniciada.'], 401);
    }

    if ($resource === 'pacientes') handlePacientesApi($pdo, $method, $id);
    if ($resource === 'consultas') handleConsultasApi($pdo, $method, $id);
    if ($resource === 'dashboard') handleDashboardApi($pdo, $method);

    apiResponse(['ok' => false, 'message' => 'Endpoint no encontrado.'], 404);
} catch (Throwable $e) {
    apiResponse(['ok' => false, 'message' => 'Error al conectar con la base de datos.'], 500);
}

function handleAuthApi(PDO $pdo, string $method, ?string $action): void
{
    if ($method !== 'POST') apiResponse(['ok' => false, 'message' => 'Método no permitido.'], 405);
    $data = apiInput();

    if ($action === 'login') {
        $username = trim($data['username'] ?? '');
        $password = (string)($data['password'] ?? '');
        if ($username === '' || $password === '') {
            apiResponse(['ok' => false, 'message' => 'Usuario y contraseña son obligatorios.'], 422);
        }

        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !(password_verify($password, $user['password']) || hash_equals((string)$user['password'], $password))) {
            apiResponse(['ok' => false, 'message' => 'Usuario o contraseña incorrectos.'], 401);
        }

        loginUser($user);
        apiResponse(['ok' => true, 'message' => 'Inicio de sesión correcto.', 'data' => currentUser()]);
    }

    if ($action === 'register') {
        $nombre = trim($data['nombre'] ?? '');
        $username = trim($data['username'] ?? '');
        $password = (string)($data['password'] ?? '');
        $rol = trim($data['rol'] ?? 'medico');

        if ($nombre === '' || $username === '' || $password === '') {
            apiResponse(['ok' => false, 'message' => 'Nombre, usuario y contraseña son obligatorios.'], 422);
        }

        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, username, password, rol) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nombre, $username, password_hash($password, PASSWORD_DEFAULT), $rol]);
        apiResponse(['ok' => true, 'message' => 'Usuario registrado correctamente.'], 201);
    }

    if ($action === 'logout') {
        logoutUser();
        apiResponse(['ok' => true, 'message' => 'Sesión cerrada correctamente.']);
    }

    apiResponse(['ok' => false, 'message' => 'Acción no encontrada.'], 404);
}

function handlePacientesApi(PDO $pdo, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = ?');
            $stmt->execute([$id]);
            $paciente = $stmt->fetch();
            if (!$paciente) apiResponse(['ok' => false, 'message' => 'Paciente no encontrado.'], 404);
            apiResponse(['ok' => true, 'data' => $paciente]);
        }

        $q = trim($_GET['search'] ?? $_GET['q'] ?? '');
        if ($q !== '') {
            $stmt = $pdo->prepare('SELECT * FROM pacientes WHERE nombre LIKE ? OR id = ? ORDER BY fecha_registro DESC');
            $stmt->execute(['%' . $q . '%', is_numeric($q) ? (int)$q : 0]);
        } else {
            $stmt = $pdo->query('SELECT * FROM pacientes ORDER BY fecha_registro DESC');
        }
        apiResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    $data = apiInput();

    if ($method === 'POST') {
        $errors = validatePaciente($data);
        if ($errors) apiResponse(['ok' => false, 'message' => implode(' ', $errors), 'errors' => $errors], 422);

        $stmt = $pdo->prepare('INSERT INTO pacientes (nombre, edad, genero, alergias, antecedentes, descriptor_facial) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($data['nombre']),
            (int)$data['edad'],
            trim($data['genero']),
            trim($data['alergias'] ?? '') ?: null,
            trim($data['antecedentes'] ?? '') ?: null,
            trim($data['descriptor_facial'] ?? '') ?: null,
        ]);
        apiResponse(['ok' => true, 'message' => 'Paciente registrado correctamente.', 'data' => ['id' => (int)$pdo->lastInsertId()]], 201);
    }

    if ($method === 'PUT' && $id) {
        $errors = validatePaciente($data);
        if ($errors) apiResponse(['ok' => false, 'message' => implode(' ', $errors), 'errors' => $errors], 422);

        $stmt = $pdo->prepare('UPDATE pacientes SET nombre=?, edad=?, genero=?, alergias=?, antecedentes=?, descriptor_facial=? WHERE id=?');
        $stmt->execute([
            trim($data['nombre']),
            (int)$data['edad'],
            trim($data['genero']),
            trim($data['alergias'] ?? '') ?: null,
            trim($data['antecedentes'] ?? '') ?: null,
            trim($data['descriptor_facial'] ?? '') ?: null,
            $id,
        ]);
        apiResponse(['ok' => true, 'message' => 'Paciente actualizado correctamente.']);
    }

    if ($method === 'DELETE' && $id) {
        $stmt = $pdo->prepare('DELETE FROM pacientes WHERE id = ?');
        $stmt->execute([$id]);
        apiResponse(['ok' => true, 'message' => 'Paciente eliminado correctamente.']);
    }

    apiResponse(['ok' => false, 'message' => 'Método no permitido.'], 405);
}

function handleConsultasApi(PDO $pdo, string $method, ?int $id): void
{
    if ($method === 'GET') {
        $sql = 'SELECT c.*, p.nombre AS paciente_nombre, t.nivel_urgencia, t.confianza, t.recomendacion
                FROM consultas c
                INNER JOIN pacientes p ON p.id = c.paciente_id
                LEFT JOIN triage t ON t.consulta_id = c.id';
        if ($id) {
            $stmt = $pdo->prepare($sql . ' WHERE c.id = ?');
            $stmt->execute([$id]);
            $consulta = $stmt->fetch();
            if (!$consulta) apiResponse(['ok' => false, 'message' => 'Consulta no encontrada.'], 404);
            apiResponse(['ok' => true, 'data' => $consulta]);
        }
        $stmt = $pdo->query($sql . ' ORDER BY c.fecha_consulta DESC');
        apiResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    $data = apiInput();

    if (($method === 'POST') || ($method === 'PUT' && $id)) {
        $errors = validateConsulta($data);
        if ($errors) apiResponse(['ok' => false, 'message' => implode(' ', $errors), 'errors' => $errors], 422);

        $pdo->beginTransaction();
        if ($method === 'POST') {
            $stmt = $pdo->prepare('INSERT INTO consultas (paciente_id, sintomas, temperatura, frecuencia_cardiaca, presion_arterial, saturacion_oxigeno, nivel_dolor, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$data['paciente_id'], trim($data['sintomas']), (float)$data['temperatura'], (int)$data['frecuencia_cardiaca'], trim($data['presion_arterial'] ?? '') ?: null, (int)$data['saturacion_oxigeno'], (int)$data['nivel_dolor'], trim($data['observaciones'] ?? '') ?: null]);
            $id = (int)$pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare('UPDATE consultas SET paciente_id=?, sintomas=?, temperatura=?, frecuencia_cardiaca=?, presion_arterial=?, saturacion_oxigeno=?, nivel_dolor=?, observaciones=? WHERE id=?');
            $stmt->execute([(int)$data['paciente_id'], trim($data['sintomas']), (float)$data['temperatura'], (int)$data['frecuencia_cardiaca'], trim($data['presion_arterial'] ?? '') ?: null, (int)$data['saturacion_oxigeno'], (int)$data['nivel_dolor'], trim($data['observaciones'] ?? '') ?: null, $id]);
        }

        $triage = calcularTriage($data);
        $triageStmt = $pdo->prepare('INSERT INTO triage (consulta_id, nivel_urgencia, confianza, recomendacion) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nivel_urgencia=VALUES(nivel_urgencia), confianza=VALUES(confianza), recomendacion=VALUES(recomendacion), fecha=CURRENT_TIMESTAMP');
        $triageStmt->execute([$id, $triage['nivel_urgencia'], $triage['confianza'], $triage['recomendacion']]);
        $pdo->commit();

        apiResponse(['ok' => true, 'message' => $method === 'POST' ? 'Consulta registrada correctamente.' : 'Consulta actualizada correctamente.', 'data' => ['id' => $id]], $method === 'POST' ? 201 : 200);
    }

    if ($method === 'DELETE' && $id) {
        $stmt = $pdo->prepare('DELETE FROM consultas WHERE id = ?');
        $stmt->execute([$id]);
        apiResponse(['ok' => true, 'message' => 'Consulta eliminada correctamente.']);
    }

    apiResponse(['ok' => false, 'message' => 'Método no permitido.'], 405);
}

function handleDashboardApi(PDO $pdo, string $method): void
{
    if ($method !== 'GET') apiResponse(['ok' => false, 'message' => 'Método no permitido.'], 405);

    $recientes = $pdo->query('SELECT id, nombre, edad, genero, fecha_registro FROM pacientes ORDER BY fecha_registro DESC LIMIT 5')->fetchAll();
    apiResponse(['ok' => true, 'data' => [
        'total_pacientes' => (int)$pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn(),
        'total_consultas' => (int)$pdo->query('SELECT COUNT(*) FROM consultas')->fetchColumn(),
        'casos_criticos' => (int)$pdo->query("SELECT COUNT(*) FROM triage WHERE nivel_urgencia IN ('Crítica', 'Critica', 'Crítico', 'Critico')")->fetchColumn(),
        'casos_altos' => (int)$pdo->query("SELECT COUNT(*) FROM triage WHERE nivel_urgencia IN ('Alta', 'Alto')")->fetchColumn(),
        'pacientes_recientes' => $recientes,
    ]]);
}
