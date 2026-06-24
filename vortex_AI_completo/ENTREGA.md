# Vortex AI Assistant - Archivos faltantes generados

## Archivos agregados

- `.htaccess`
- `api.php`
- `router.php`
- `index.php`
- `login.php`
- `register.php`
- `logout.php`
- `historial.php`
- `includes/conn.php`
- `includes/auth.php`
- `includes/layout.php`
- `includes/layout_end.php`
- `includes/validation.php`
- `consultas/index.php`
- `consultas/crear.php`
- `consultas/editar.php`
- `consultas/eliminar.php`
- `consultas/form.php`
- `database/schema.sql`

## Archivos corregidos

- `login.html`
- `signup.html`
- `pacientes/crear.php`
- `pacientes/editar.php`

## Funcionalidad cubierta

- Autenticación funcional con `usuarios`.
- CRUD completo de pacientes.
- CRUD completo de consultas médicas.
- Triage automático inicial por reglas clínicas, listo para reemplazarse por IA.
- Dashboard con datos reales.
- Historial clínico por paciente.
- Búsqueda de pacientes por nombre o ID.
- Endpoints JSON solicitados.
- SQL instalable para la base de datos objetivo.

## Endpoints

- `POST /auth/login`
- `POST /auth/register`
- `POST /auth/logout`
- `GET /pacientes`
- `GET /pacientes/:id`
- `POST /pacientes`
- `PUT /pacientes/:id`
- `DELETE /pacientes/:id`
- `GET /consultas`
- `GET /consultas/:id`
- `POST /consultas`
- `PUT /consultas/:id`
- `DELETE /consultas/:id`
- `GET /dashboard`

Si Apache no tiene `mod_rewrite`, usa el respaldo:

- `api.php?resource=pacientes`
- `api.php?resource=consultas`
- `api.php?resource=dashboard`
- `api.php?resource=auth&action=login`

## Ejecución

1. Copia la carpeta del proyecto a `htdocs/vortex_AI`.
2. Importa `database/schema.sql` en MySQL/MariaDB.
3. Revisa credenciales en `includes/conn.php`.
4. Abre `http://localhost/vortex_AI/login.php`.
5. Usuario inicial: `admin`
6. Contraseña inicial: `admin123`

Servidor embebido para pruebas:

```bash
php -S localhost:8000 router.php
```
