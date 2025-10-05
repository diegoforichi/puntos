### **📝 Consideraciones para Desarrollo en el Hosting Compartido Actual**

Este documento está dirigido a un asistente de IA para que comprenda las limitaciones y peculiaridades del entorno de hosting compartido donde se despliega esta aplicación. Tener en cuenta estos puntos evitará errores recurrentes y agilizará el desarrollo.

#### **1. Base de Datos: Versiones y Compatibilidad**

*   **Problema Detectado:** La versión de **SQLite** en el servidor no es la más reciente. Esto nos obligó a usar sintaxis más antigua y compatible.
*   **Consideración para el Asistente:**
    *   **No uses `INSERT ... ON CONFLICT`**. En su lugar, utiliza `INSERT OR REPLACE INTO`.
    *   Cualquier consulta que escribas debe ser compatible tanto con MySQL 8 (para producción) como con una versión más conservadora de SQLite (para los demos). Evita funciones o sintaxis exclusivas de una sola versión o motor de base de datos.
    *   El acceso a **MySQL** se realiza principalmente a través de **phpMyAdmin**. No hay acceso a la línea de comandos de MySQL ni privilegios de `SUPER` usuario, por lo que la aplicación no puede crear/modificar triggers, stored procedures o eventos a nivel de base de datos.

#### **2. Servidor Web y Seguridad: Firewall Agresivo**

*   **Problema Detectado:** El firewall del hosting (**ModSecurity**) es muy estricto y ha bloqueado archivos legítimos, como `index.php`, por falso positivo, impidiendo que la aplicación funcione.
*   **Consideración para el Asistente:**
    *   Si después de un despliegue, una URL específica (especialmente el `index.php` que actúa como front-controller) devuelve un error 403 Forbidden o una página de error del hosting, es muy probable que sea el firewall.
    *   La solución es contactar al soporte del hosting y solicitar que añadan una **regla de excepción (whitelist)** para el archivo o la regla de ModSecurity que se está activando. No se puede solucionar desde el código.

#### **3. Entorno PHP: Funciones Deshabilitadas**

*   **Problema Detectado:** Como en la mayoría de hostings compartidos, ciertas funciones de PHP que interactúan con el sistema operativo están deshabilitadas por seguridad.
*   **Consideración para el Asistente:**
    *   **No intentes usar `exec()`, `shell_exec()`, `system()`, `passthru()`, `proc_open()` o `symlink()`**. No funcionarán.
    *   Cualquier tarea que normalmente harías con un comando de terminal (como crear un archivo ZIP, mover archivos masivamente, etc.) debe realizarse utilizando **funciones nativas de PHP** (ej. la clase `ZipArchive`).

#### **4. Ejecución de Tareas: Cron Jobs Limitados**

*   **Problema Detectado:** La configuración de tareas programadas (cron jobs) en el panel del hosting puede ser limitada. A menudo no permite ejecutar comandos PHP directamente desde la CLI (`php /ruta/al/script.php`).
*   **Consideración para el Asistente:**
    *   La forma más fiable de ejecutar un cron es a través de **`wget` o `curl`**, llamando a una URL. Esto significa que el script del cron debe ser accesible públicamente.
    *   **IMPORTANTE:** Protege los scripts de cron con un `secret_key` como parámetro en la URL para evitar que cualquiera los ejecute.
    *   Recuerda el bug del **singleton de la base de datos**. En cualquier cron que procese múltiples tenants, es **OBLIGATORIO** llamar a `Database::clearInstance()` entre cada tenant para no mezclar los datos.

#### **5. Despliegue y Herramientas: Sin Acceso a CLI**

*   **Problema Detectado:** No hay acceso SSH o es muy limitado. Esto impide el uso de herramientas estándar de desarrollo en el servidor.
*   **Consideración para el Asistente:**
    *   **No puedes ejecutar `composer install` en el servidor**. El directorio `vendor/` debe ser subido íntegramente por FTP/SFTP.
    *   **No puedes ejecutar `git pull` en el servidor**. El despliegue es completamente manual, subiendo los archivos modificados.

#### **6. Envío de Email: PHP `mail()` no fiable**

*   **Problema Detectado:** La función `mail()` de PHP es poco fiable en este entorno y los correos a menudo terminan en spam o no se entregan.
*   **Consideración para el Asistente:**
    *   Utiliza **siempre SMTP** para el envío de correos. La aplicación ya está configurada con PHPMailer para esto.
    *   Asegúrate de que las credenciales SMTP (host, usuario, contraseña, puerto, seguridad) estén correctamente configuradas en el entorno de producción.
