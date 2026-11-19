<p align="center">
  <img src="public/assets/img/photo.jpg" alt="Escudo U.E. Josefina Pimentel" width="120">
</p>

<h1 align="center">Sistema Web Administrativo — U.E. Josefina Pimentel</h1>

<p align="center">
  Plataforma web para la digitalización, gestión y resguardo de <strong>Actas de Graduación</strong>, <strong>Expedientes y Justificativos Médicos del Personal</strong>, <strong>Control de Usuarios por Roles</strong> y <strong>Auditoría Inalterable</strong>.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8">
  <img src="https://img.shields.io/badge/MySQL-PDO-4479A1?style=flat&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Arquitectura-MVC-0d6efd?style=flat" alt="MVC">
</p>

<p align="center">
  🌐 <strong>Portal en línea:</strong> <a href="https://uejosefinapimentel.infinityfree.me">https://uejosefinapimentel.infinityfree.me</a> ·
  📘 <a href="Manual_de_Usuario_UE_Josefina_Pimentel.pdf">Manual de Usuario (PDF)</a>
</p>

---

## 📋 Acerca del Proyecto

El **Portal Administrativo Digital de la U.E. Josefina Pimentel** es una aplicación web desarrollada bajo el patrón **MVC (Modelo–Vista–Controlador)** en PHP 8 nativo y MySQL. Fue diseñada para modernizar la gestión documental del plantel, protegiendo los libros históricos de graduación y centralizando los expedientes médicos y laborales del personal desde computadoras, tablets y dispositivos móviles.

---

## ✨ Funcionalidades Principales

### 🎓 1. Módulo de Actas de Graduación (`/actas`)
* **Registro por Año Escolar:** Creación y edición de actas con numeración correlativa automática (`Acta #1, #2...`) y nómina digital de graduados (Apellidos y Nombres, Cédula, Fecha de Nacimiento, Literal y Observación).
* **Respaldo Fotográfico del Libro Físico:** Carga de hasta **4 fotografías** originales por acta con visor ampliado (zoom) y descarga en calidad original.
* **Buscador Inteligente Multicriterio:** Búsqueda por nombre/apellido (insensible a orden, mayúsculas y acentos), cédula, año escolar, fecha de nacimiento o estado del acta (`Activas` / `Inactivas`).
* **Vista Previa, Impresión y Exportación PDF:** Generación de actas digitales y respaldos fotográficos en formato **Carta** listos para imprimir o guardar en PDF.

### 📁 2. Módulo de Expedientes de Personal y Justificativos Médicos (`/personal`)
* **Directorio del Personal:** Registro de personal **Docente, Administrativo, Directivo y Obrero** con foto de perfil, datos de contacto, fecha de nacimiento (`DD-MM-AAAA`), cargo y estado laboral (`Activo` / `Inactivo`).
* **Alerta de Condición Médica:** Etiqueta visual inmediata en la ficha y listado si el trabajador presenta alguna patología registrada.
* **Control de Justificativos Médicos y Vencimientos:** Archivo de reposos, justificativos y constancias en formato **Imagen** o **PDF**, con etiquetas automáticas de estado (`Vigente` en verde / `VENCIDO` en rojo), descarga directa e impresión en formato Carta junto con la ficha del trabajador.

### 🛡️ 3. Gestión de Usuarios y Roles (`/usuarios`)
* **Jerarquía de 3 Niveles:**
  * **Admin Mayor:** Control total del sistema; único autorizado para gestionar Administradores y Usuarios Regulares.
  * **Administrador:** Gestión de actas, expedientes, auditoría, cuentas regulares y cambio de su propia contraseña.
  * **Regular:** Operación diaria sobre Actas de Graduación y Expedientes de Personal.
* **Estado en Tiempo Real:** Indicador `Conectado` / `Desconectado` mediante pulso activo y limpieza automática al cerrar o expirar la sesión.
* **Confirmación Segura:** Temporizador de seguridad de **5 segundos** antes de eliminar o desactivar registros sensibles.

### 📜 4. Bitácora de Auditoría (`/auditoria`)
* **Logs Inalterables:** Registro automático de solo lectura con fecha y hora oficial de Venezuela (`America/Caracas`, formato `DD-MM-AAAA HH:MM:SS`), usuario responsable, acción detallada e IP de origen.
* **Filtros Dinámicos (AJAX):** Consulta instantánea por período (`Hoy`, `Semanal`, `Mensual`, `Todos`) y orden (`Ascendente` / `Descendente`) sin recargar la página.

---

## 🔒 Seguridad y Rendimiento

* **Seguridad:** Contraseñas encriptadas con `Bcrypt` (`password_hash`), protección contra inyección SQL mediante consultas preparadas `PDO`, tokens `CSRF` en formularios, sanitización contra `XSS`, redirección automática a `HTTPS` y páginas controladas de contingencia (`404` y `500`).
* **Optimización de Imágenes:** Conversión automática a **WebP** y generación de miniaturas (`320px`) conservando los archivos originales para zoom y descarga, carga diferida (`loading="lazy"`), caché HTTP con `ETag` (`304 Not Modified`) y liberación temprana de bloqueo de sesión (`session_write_close`) para descarga en paralelo.
* **Diseño 100% Responsivo:** Adaptado a escritorio, tablets y teléfonos móviles.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8 (Arquitectura MVC)
* **Base de Datos:** MySQL / MariaDB (PDO)
* **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, JavaScript (ES6 / Fetch API)
* **Exportación PDF:** `html2pdf.js` y hojas de estilo `@media print`
* **Servidor Web:** Apache con `mod_rewrite`, `mod_deflate` y `mod_expires`

---

## 📂 Estructura del Proyecto

```text
josefinapimentel/
├── app/
│   ├── controllers/        # ActasController, AdminController, AuthController, DashboardController, LogsController, PersonalController
│   ├── core/               # Controller, ImageOptimizer, Security
│   ├── models/             # Acta, AuditLog, Personal, UserModel
│   └── views/
│       ├── layouts/        # main.php, 404.php, 500.php
│       ├── modals/         # modals_actas.php, modals_personal.php, modals_usuarios.php
│       ├── partials/       # navbar.php, footer.php, alerts.php
│       └── *.php           # Vistas de cada módulo e impresión
├── config/
│   └── Database.php        # Conexión PDO con soporte local y variables de entorno
├── public/
│   ├── assets/             # css/, js/, img/
│   ├── uploads/            # Archivos adjuntos y caché WebP (no versionados)
│   ├── .htaccess           # Reescritura al enrutador frontal
│   └── index.php           # Front Controller y manejo global de errores
├── .htaccess               # Redirección HTTPS y ruteo hacia public/
└── Manual_de_Usuario_UE_Josefina_Pimentel.pdf
```

---

## ⚙️ Instalación Local (XAMPP)

1. Clona o copia el proyecto dentro de `htdocs`:
   ```bash
   git clone <URL_DEL_REPOSITORIO> c:/xampp/htdocs/josefinapimentel
   ```
2. Inicia los servicios **Apache** y **MySQL** desde el panel de control de **XAMPP**.
3. Crea la base de datos `uejosefinapimentel` en phpMyAdmin e importa el respaldo `.sql` del sistema.
4. Configura las credenciales de conexión en `config/Database.php` (o mediante variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
5. Abre el navegador en:
   ```text
   http://localhost/josefinapimentel/
   ```

> **Requisitos de PHP:** PHP 8.1+ con extensiones `pdo_mysql`, `gd` (con soporte WebP), `mbstring` y `fileinfo`, además de `mod_rewrite` habilitado en Apache.

---

## 👥 Autores

Proyecto desarrollado en la **Universidad Politécnica Territorial del Estado Trujillo "Mario Briceño Iragorry" (UPTTMBI)** para la **Unidad Educativa "Josefina Pimentel"**:

* **Luis Márquez**
* **Fernando Valera**
* **Nayson Medina**

*© 2026 U.E. Josefina Pimentel — Sistema Administrativo.*
