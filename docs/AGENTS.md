# Guía para Agentes – Proyecto Sistema de Puntos

## 1. Principios de Trabajo
- Siempre presentar un **plan resumido** antes de ejecutar cambios significativos.
- Leer el contexto existente (código y documentación) antes de modificar archivos.
- Evitar dependencias externas; todo debe residir dentro del repositorio (sin instalar paquetes nuevos en hosting).
- Mantener el estilo del proyecto (Laravel 10, Blade con Bootstrap 5 desde CDN, JS vanilla).
- Respetar preferencias del usuario: respuestas en español, explicaciones paso a paso, evitar cambios disruptivos.

## 2. Flujo de Desarrollo Recomendado
1. Analizar requerimiento y documentación vigente.
2. Elaborar plan detallado y validarlo con el usuario.
3. Antes de tocar bases de datos (MySQL o SQLite), repasar `docs/ARQUITECTURA.md` y `docs/CHECKLIST_TAREAS.md` para asegurar el esquema único vigente.
4. Actualizar código, migraciones y documentación en conjunto.
4. Ejecutar pruebas locales (emulador, artisan commands) cuando sea posible.
5. Documentar al cierre: qué se modificó, cómo probarlo y pasos siguientes.

## 3. Estándares de Código
- PHP: PSR-12, usar helpers Laravel (`config`, `DB`, `now()`).
- Migraciones SQLite: evitar `change()`; comprobar existencia de columnas antes de crear.
- Rutas: agrupar por middleware; usar nombres consistentes (`tenant.configuracion.*`).
- Vistas Blade: preferir componentes existentes; mantener textos en español.
- Servicios: inyectar dependencias vía constructor (ej. `PuntosService` recibe `Tenant`).

## 4. Pruebas y Herramientas
- `scripts/emulador_webhook.php` admite flags `--cfeid`, `--doc-mode`, `--monto` para validar reglas multitenant.
- Revisar `docs/CHECKLIST_TAREAS.md` antes de considerar una entrega lista.
- Para SQLite de tenants usar comandos personalizados (`tenant:setup-database`, etc.).
- No ejecutar migraciones automáticamente al visualizar un tenant en el panel; sólo usar el botón manual “Re-ejecutar migraciones” si se requiere.

## 5. Documentación
- Actualizar `CHANGELOG.md`, `MANUAL_USUARIO.md`, `docs/ARQUITECTURA.md` y `docs/CHECKLIST_TAREAS.md` cuando se agreguen o modifiquen funcionalidades.
- Mantener `agents.md` actualizado con nuevas prácticas, hallazgos o decisiones relevantes.

## 6. Comunicación
- Señalar riesgos o incertidumbres; proponer alternativas cuando existan varias opciones.
- Acordar con el usuario las pruebas y despliegues (el usuario realiza testing local y hosting al final).
- Registrar credenciales generadas o endpoints en mensajes finales (sin exponer contraseñas previas).
