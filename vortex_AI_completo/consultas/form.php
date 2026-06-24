<div class="mb-3">
  <label class="form-label fw-semibold">Paciente <span class="text-danger">*</span></label>
  <select name="paciente_id" class="form-select" required>
    <option value="">— Seleccionar paciente —</option>
    <?php foreach ($pacientes as $paciente): ?>
      <option value="<?= (int)$paciente['id'] ?>" <?= (string)$datos['paciente_id'] === (string)$paciente['id'] ? 'selected' : '' ?>>
        #<?= (int)$paciente['id'] ?> · <?= e($paciente['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<div class="mb-3">
  <label class="form-label fw-semibold">Síntomas <span class="text-danger">*</span></label>
  <textarea name="sintomas" class="form-control" rows="4" required><?= e($datos['sintomas']) ?></textarea>
</div>

<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label fw-semibold">Temperatura °C <span class="text-danger">*</span></label>
    <input type="number" step="0.1" min="30" max="45" name="temperatura" class="form-control" value="<?= e($datos['temperatura']) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">Frecuencia cardíaca <span class="text-danger">*</span></label>
    <input type="number" min="20" max="250" name="frecuencia_cardiaca" class="form-control" value="<?= e($datos['frecuencia_cardiaca']) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">Presión arterial</label>
    <input name="presion_arterial" class="form-control" placeholder="120/80" value="<?= e($datos['presion_arterial']) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">Saturación O₂ % <span class="text-danger">*</span></label>
    <input type="number" min="0" max="100" name="saturacion_oxigeno" class="form-control" value="<?= e($datos['saturacion_oxigeno']) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">Nivel de dolor <span class="text-danger">*</span></label>
    <input type="number" min="1" max="10" name="nivel_dolor" class="form-control" value="<?= e($datos['nivel_dolor']) ?>" required>
  </div>
</div>

<div class="mb-4 mt-3">
  <label class="form-label fw-semibold">Observaciones</label>
  <textarea name="observaciones" class="form-control" rows="3"><?= e($datos['observaciones']) ?></textarea>
</div>
