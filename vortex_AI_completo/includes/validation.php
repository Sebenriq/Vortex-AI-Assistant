<?php
declare(strict_types=1);

function validatePaciente(array $data): array
{
    $errors = [];
    $edad = filter_var($data['edad'] ?? null, FILTER_VALIDATE_INT);

    if (trim($data['nombre'] ?? '') === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    if (($data['edad'] ?? '') === '') {
        $errors[] = 'La edad es obligatoria.';
    } elseif ($edad === false || $edad <= 0) {
        $errors[] = 'La edad debe ser un número mayor a 0.';
    } elseif ($edad >= 120) {
        $errors[] = 'La edad debe ser menor a 120.';
    }

    if (!in_array($data['genero'] ?? '', ['Masculino', 'Femenino', 'Otro'], true)) {
        $errors[] = 'El género es obligatorio.';
    }

    return $errors;
}

function validateConsulta(array $data): array
{
    $errors = [];

    if (empty($data['paciente_id']) || filter_var($data['paciente_id'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Selecciona un paciente válido.';
    }

    if (trim($data['sintomas'] ?? '') === '') {
        $errors[] = 'Los síntomas son obligatorios.';
    }

    validateRange($errors, $data, 'temperatura', 'La temperatura', 30, 45);
    validateRange($errors, $data, 'frecuencia_cardiaca', 'La frecuencia cardíaca', 20, 250);
    validateRange($errors, $data, 'saturacion_oxigeno', 'La saturación de oxígeno', 0, 100);
    validateRange($errors, $data, 'nivel_dolor', 'El nivel de dolor', 1, 10);

    return $errors;
}

function validateRange(array &$errors, array $data, string $key, string $label, float $min, float $max): void
{
    if (($data[$key] ?? '') === '') {
        $errors[] = "{$label} es obligatorio.";
        return;
    }

    if (!is_numeric($data[$key])) {
        $errors[] = "{$label} debe ser un número válido.";
        return;
    }

    $value = (float)$data[$key];
    if ($value < $min || $value > $max) {
        $errors[] = "{$label} debe estar entre {$min} y {$max}.";
    }
}

function calcularTriage(array $consulta): array
{
    $score = 0;
    $temperatura = (float)$consulta['temperatura'];
    $frecuencia = (int)$consulta['frecuencia_cardiaca'];
    $saturacion = (int)$consulta['saturacion_oxigeno'];
    $dolor = (int)$consulta['nivel_dolor'];

    if ($saturacion < 90) $score += 4;
    elseif ($saturacion < 94) $score += 2;

    if ($temperatura >= 40 || $temperatura <= 34) $score += 3;
    elseif ($temperatura >= 38.5) $score += 1;

    if ($frecuencia > 130 || $frecuencia < 45) $score += 3;
    elseif ($frecuencia > 110) $score += 1;

    if ($dolor >= 9) $score += 3;
    elseif ($dolor >= 7) $score += 2;
    elseif ($dolor >= 5) $score += 1;

    if ($score >= 7) {
        return ['nivel_urgencia' => 'Crítica', 'confianza' => 90.00, 'recomendacion' => 'Atención inmediata en sala de choque.'];
    }

    if ($score >= 4) {
        return ['nivel_urgencia' => 'Alta', 'confianza' => 82.00, 'recomendacion' => 'Priorizar valoración médica en urgencias.'];
    }

    if ($score >= 2) {
        return ['nivel_urgencia' => 'Media', 'confianza' => 74.00, 'recomendacion' => 'Mantener observación y reevaluar signos vitales.'];
    }

    return ['nivel_urgencia' => 'Baja', 'confianza' => 68.00, 'recomendacion' => 'Atención ambulatoria con seguimiento clínico.'];
}
