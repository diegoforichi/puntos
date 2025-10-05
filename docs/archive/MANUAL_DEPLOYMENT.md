# 🚀 Manual de Despliegue - Sistema de Puntos

**Fecha:** 02/10/2025

---

## 📋 Requisitos del Servidor

### Software Requerido
- **PHP:** 8.1 o superior
- **Extensiones PHP necesarias:**
  - `pdo`, `pdo_sqlite`, `pdo_mysql`
  - `mbstring`, `openssl`, `json`, `curl`
  - `fileinfo`, `tokenizer`, `xml`, `ctype`
- **MySQL/MariaDB:** 5.7+ / 10.3+
- **Composer:** 2.x
- **Permisos de escritura:** en `storage/` y `bootstrap/cache/`

### Opcional (para producción)
- **Supervisor** (para queues y cron jobs)
- **Nginx/Apache** con mod_rewrite
- **Túnel reverso** (cloudflared/ngrok) para exponer el webhook

---

## 📦 Paso 1: Clonar el Proyecto

```bash
cd /ruta/hosting
git clone https://github.com/tu-usuario/puntos.git
cd puntos/app
```

---

## 🔧 Paso 2: Instalar Dependencias

```bash
composer install --no-dev --optimize-autoloader
```

---

## ⚙️ Paso 3: Configurar Variables de Entorno

Copiar `.env.example` a `.env` y configurar:

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env`:

```env
APP_NAME="Sistema de Puntos"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tupuntos.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puntos_main
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

---

## 🗄️ Paso 4: Crear y Migrar Base de Datos

```bash
# Crear base de datos MySQL (vía phpMyAdmin o CLI)
mysql -u root -p -e "CREATE DATABASE puntos_main CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ejecutar migraciones globales
php artisan migrate --force

# Crear seeder SuperAdmin (si no existe)
php artisan db:seed --class=DatabaseSeeder --force
```

**Credenciales SuperAdmin por defecto:**
- Email: `superadmin@puntos.local`
- Password: `SuperAdmin123!`

⚠️ **Cambia estas credenciales después del primer login.**

---

## 📁 Paso 5: Crear Directorio de Tenants

```bash
mkdir -p storage/tenants
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # (ajustar usuario según servidor)
```

---

## 🌐 Paso 6: Configurar Servidor Web

### **Nginx (recomendado)**
```nginx
server {
    listen 80;
    server_name tupuntos.com;
    root /ruta/puntos/app/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### **Apache (.htaccess ya incluido)**
Asegurar que `mod_rewrite` esté activo.

---

## 📡 Paso 7: Configurar SMTP y WhatsApp

1. Acceder al SuperAdmin: `https://tupuntos.com/superadmin/login`
2. Ir a **Configuración Global**
3. Completar datos de **SMTP** y **WhatsApp**
4. Usar los botones **"Enviar email de prueba"** y **"Enviar WhatsApp de prueba"** para validar

---

## 🏢 Paso 8: Crear el Primer Tenant

1. En SuperAdmin → **Tenants** → **Crear nuevo tenant**
2. Completar RUT, nombre comercial, contacto
3. El sistema generará automáticamente:
   - API Key
   - Base SQLite
   - Usuarios iniciales (admin, supervisor, operario)
4. Copiar las credenciales mostradas

---

## 🔗 Paso 9: Exponer el Webhook

### **Opción A: Túnel con Cloudflared (recomendado para testing)**
```bash
cloudflared tunnel --url https://tupuntos.com/api/webhook/ingest
```

### **Opción B: Producción directa**
Configurar el proveedor del webhook para que apunte a:
- **URL:** `https://tupuntos.com/api/webhook/ingest`
- **Header:** `Authorization: Bearer {API_KEY_DEL_TENANT}`
- **Content-Type:** `application/json`

---

## ⏰ Paso 10: Configurar Cron Jobs

Agregar a crontab del servidor:

```bash
# Editar crontab
crontab -e

# Agregar esta línea (ajustar ruta según instalación)
0 8 * * * cd /ruta/puntos/app && php artisan tenant:send-daily-reports >> storage/logs/cron.log 2>&1
```

Esto enviará el resumen diario a las 8:00 AM todos los días.

---

## ✅ Paso 11: Verificación Post-Deployment

### **Checklist rápido:**
- [ ] SuperAdmin login funciona
- [ ] Tenant creado correctamente
- [ ] Webhook recibe payload de prueba (usar `scripts/emulador_webhook.php` apuntando al hosting)
- [ ] Email de prueba enviado correctamente
- [ ] WhatsApp de prueba enviado correctamente
- [ ] Panel del tenant accesible (`/RUT/login`)
- [ ] Favicon visible en navegador

### **Comando de prueba del webhook:**
```bash
php scripts/emulador_webhook.php \
  --url=https://tupuntos.com/api/webhook/ingest \
  --rut=000000000016 \
  --api-key=TU_API_KEY_AQUI \
  --doc-mode=ci \
  --cfeid=101 \
  --monto=5000 \
  --moneda=UYU
```

---

## 🔒 Seguridad Post-Deployment

1. **Cambiar credenciales SuperAdmin** inmediatamente.
2. **Regenerar API Keys** de tenants de prueba si expusiste alguno público.
3. **Activar HTTPS** (certificado Let's Encrypt recomendado).
4. **Configurar firewall** para limitar acceso a MySQL (solo localhost).
5. **Revisar permisos** de `storage/` (775 max, nunca 777).

---

## 📞 Soporte

Para issues o mejoras, revisar:
- `docs/ARQUITECTURA.md` (detalles técnicos)
- `MANUAL_USUARIO.md` (funcionalidades)
- `docs/CHECKLIST_TAREAS.md` (pendientes)
- `docs/AGENTS.md` (guía para desarrollo)
