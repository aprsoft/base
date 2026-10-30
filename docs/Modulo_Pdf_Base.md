# Módulo PDF para Base — parche propuesto

**Origen de referencia**: `aprsoft/base`, rama `main`, commit `e0b06d613a42716e53c03b7a11c8cf481e629716` (23-10-2026).

**Destino recomendado**: rama `feature/pdf-documents`. No aplicar directamente en `main`.

## Objetivo

Añadir a `ShowUser` tres acciones: descargar, visualizar/imprimir desde el visor del navegador y solicitar el envío del perfil por correo electrónico. La implementación se organiza en `app/Pdf/`, `app/Services/Pdf/`, `app/Http/Controllers/PdfController.php` y `app/Livewire/Pdf/`.

Los identificadores de documento se permiten explícitamente en `PdfRegistry`; el único tipo registrado inicialmente es `user.profile`. Para nuevos tipos de documentos hay que añadir una definición que implemente `PdfDocument`.

## Seguridad

- Todas las acciones vuelven a autorizarse en el servidor.
- Se conserva la prohibición para el usuario ID 1, los roles `SuperAdmin` y la lectura de usuarios suspendidos; se verifican permisos `users.index` (ver/descargar) y `users.edit` adicional (correo).
- El envío por cola vuelve a verificar el usuario solicitante activo y sus permisos al ejecutarse.
- No se admiten vistas ni clases dinámicas desde HTTP.
- Se limita la tasa de solicitudes de correo por cuenta (5 por 60 segundos) y se pide confirmar el destinatario.
- El PDF se genera al ejecutar el Job y se adjunta en memoria; no se expone como archivo público.
- `noSandbox()` no se inserta en el código. La configuración global de Spatie debe verificarse en Lerd.

## Límites conocidos

- Solo se implementa el caso de usuario central (`web`). La instancia actual de `composer.lock` no incluye `stancl/tenancy`.
- El correo del PDF contiene datos actuales en el momento de ejecución del Job, no una instantánea al presionar Enviar; no reutilizar este comportamiento en DTE emitidos.
- El permiso de envío inicial reutiliza `users.edit`; para documentos fiscales o confidenciales deben definirse permisos específicos.
- No se implementan versiones históricas ni auditoría de distribución; pueden añadirse cuando exista un requisito.
- Los ajustes visuales del modal y la compatibilidad completa con las versiones locales de MaryUI/Livewire deben probarse en Lerd.
- El worker y el mailer deben estar operativos; `QUEUE_CONNECTION=sync` puede ejecutar la generación sincrónicamente.

## Dependencias

Se utilizan las dependencias ya presentes: `spatie/laravel-pdf`, `spatie/browsershot`, `spatie/laravel-permission`, `livewire` y Puppeteer. Este parche **no** modifica Composer, npm, .env ni bases de datos.

Revisar previamente `1.- documentation/Guia_Spatie_Laravel_PDF_Lerd_Linux_Mint.md` para configurar las rutas reales de Chromium y `node_modules` en Lerd mediante las opciones oficiales de Spatie:

- `LARAVEL_PDF_DRIVER=browsershot`
- `LARAVEL_PDF_CHROME_PATH` (comprobar dentro del contenedor)
- `LARAVEL_PDF_NODE_MODULES_PATH` (comprobar dentro del contenedor)
- `LARAVEL_PDF_NO_SANDBOX` únicamente si el contenedor de desarrollo lo exige.

## Verificaciones pendientes en el entorno local

- Comparar el HEAD local con el commit de referencia y comprobar que no existan cambios sin guardar.
- Ejecutar `git apply --check` antes de aplicar el parche.
- Validar PHP (`php -l`), rutas, permisos y pruebas Pest (`tests/Feature/PdfDocumentTest.php`, `tests/Feature/UserShowTest.php`).
- Confirmar configuración efectiva de Chromium/Puppeteer y generar un PDF real mediante HTTP.
- Verificar envío de correo, worker y recepción del adjunto en Mailpit u otro transporte permitido.
- Registrar un commit en `feature/pdf-documents` y subir esa rama tras las pruebas.

**Estado**: código preparado y comprobado sintácticamente para PHP, pero no instalado ni ejecutado en Base/Lerd. `main` de GitHub permanece intacta.
