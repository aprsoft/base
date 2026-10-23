# Guía de instalación de Spatie Laravel PDF en Lerd

**Ámbito:** Linux Mint 22.3, Lerd, Podman, PHP 8.4, Laravel 12 y controlador Browsershot.  
**Fecha de documentación:** 23 de octubre de 2026.  
**Objetivo:** configurar la generación de PDF desde Laravel usando Spatie PDF → Browsershot → Puppeteer → Chromium, evitando descargar un segundo navegador.

> **Importante:** esta guía documenta una instalación que **sí funcionó** en `~/Proyectos/base`, y añade procedimientos recomendados para proyectos futuros. Las instrucciones marcadas como «recomendadas» no se ejecutaron durante esa verificación. Ejecuta **un solo comando por paso**, comprueba el resultado y detente si falla. No copies comandos de eliminación sin revisar su impacto.

## 1. Resultado confirmado y versiones del caso probado

| Componente | Versión observada | Evidencia |
|---|---|---|
| PHP del contenedor Lerd | 8.4 | Imagen `lerd-php84-fpm` construida correctamente |
| Node.js en Lerd | v24.18.1 | `node --version` en el contenedor |
| Chromium en Lerd | 155.0.8059.84 Alpine Linux | `chromium --version` en el contenedor |
| `spatie/laravel-pdf` | 2.14.0 | `composer show 'spatie/*'` |
| `spatie/browsershot` | 5.4.1 | `composer show 'spatie/*'` |
| Puppeteer | 25.16.0 | `npm ls puppeteer --depth=0` |
| Generación desde Laravel | Correcta | PDF 1.4 de una página, validado con `file` |

La **causa del primer fallo** de instalación de Chromium no quedó demostrada. El mensaje `Connection aborted` / `unexpected end of file` fue compatible con una interrupción transitoria, y el segundo intento terminó correctamente; no debe afirmarse que la conexión a Internet fue la causa confirmada.

## 2. Arquitectura y requisitos

- **Lerd** ejecuta PHP en contenedores basados en Alpine Linux y utiliza Podman.
- **Chromium** debe estar disponible en el mismo entorno en que se ejecuta PHP/Browsershot (el contenedor PHP de Lerd), no solamente en Linux Mint.
- **Node.js** debe estar disponible dentro del contenedor. En el caso verificado, ya venía disponible y no hubo que instalarlo.
- **Puppeteer** se instala **en cada proyecto Laravel** mediante npm. Lerd montó `/home/ro` del anfitrión como `/home/ro` dentro del contenedor, por lo que se pudo acceder al `node_modules` del proyecto.
- Spatie Laravel PDF v2 requiere PHP 8.2 o posterior y Laravel 11 o posterior, según su documentación. Comprueba las versiones y dependencias reales de cada nuevo proyecto antes de instalar.
- Si un proyecto usa otra versión de PHP en Lerd, verifica que su imagen contenga Chromium antes de continuar.

## 3. Preparación de Lerd: se hace una sola vez por instalación

### Paso 3.1 — Consultar los paquetes configurados (recomendado)

Desde una terminal en Linux Mint:

```bash
lerd php:pkg list
```

Revisa si `chromium` ya figura entre los paquetes. **Si está instalado para la versión de PHP del sitio, no lo reinstales.**

### Paso 3.2 — Instalar Chromium si falta (ejecutado y exitoso)

```bash
lerd php:pkg add chromium
```

Este comando agrega el paquete Alpine a la configuración de los contenedores PHP gestionados por Lerd. Reconstruye la imagen PHP en uso y las otras versiones se reconstruyen cuando corresponda. La configuración del paquete se conserva para reconstrucciones posteriores.

La salida observada durante la instalación exitosa incluía:

```text
OK: 1000.1 MiB in 294 packages
PHP 8.4 image built successfully.
✓ packages installed for PHP 8.4
```

**Precaución:** reconstruye/reinicia el contenedor de la versión PHP afectada. No lo hagas sin considerar los proyectos que estén en ejecución.

### Paso 3.3 — Confirmar Chromium (ejecutado y exitoso en PHP 8.4)

```bash
podman exec lerd-php84-fpm chromium --version
```

Resultado real:

```text
Chromium 155.0.8059.84 Alpine Linux
```

Si el sitio usa una versión PHP diferente, sustituye el nombre del contenedor **solo después de identificarlo**.

### Paso 3.4 — Confirmar Node.js (ejecutado y exitoso en PHP 8.4)

```bash
podman exec lerd-php84-fpm node --version
```

Resultado real: `v24.18.1`.

### Paso 3.5 — Localizar la ruta real de Chromium (recomendado)

Dentro del directorio de un sitio vinculado a Lerd:

```bash
lerd php -r 'echo trim(shell_exec("command -v chromium")), PHP_EOL;'
```

Anota **la ruta devuelta**: se usará al configurar el proyecto. No presupongas una ruta sin comprobarla; `/usr/bin/chromium` es solo un ejemplo posible en Alpine.

## 4. Preparación de cada proyecto Laravel

> Antes de instalar paquetes en un repositorio existente, comprueba que la rama principal `main` esté actualizada, que no haya cambios sin respaldar y que el proyecto cumpla los requisitos de Spatie. No sustituir dependencias ni versiones sin necesidad.

### Paso 4.1 — Entrar al proyecto (recomendado)

Sustituye `NOMBRE_PROYECTO` por el directorio real:

```bash
cd ~/Proyectos/NOMBRE_PROYECTO
```

Los comandos siguientes de esta sección se ejecutan desde la raíz del proyecto.

### Paso 4.2 — Instalar Spatie Laravel PDF (recomendado; documentado oficialmente)

```bash
lerd composer require spatie/laravel-pdf
```

### Paso 4.3 — Instalar el controlador Browsershot (recomendado; documentado oficialmente)

```bash
lerd composer require spatie/browsershot
```

El paquete PDF permite otros controladores, pero esta guía utiliza **Browsershot**. No instales todos los controladores posibles.

### Paso 4.4 — Instalar Puppeteer sin descargar otro navegador (recomendado)

Desde la raíz del proyecto, con npm operativo en Linux Mint:

```bash
PUPPETEER_SKIP_DOWNLOAD=1 npm install puppeteer
```

La variable de entorno se aplica a **esta instalación** y evita la descarga automática de Chrome por Puppeteer. Se utilizará el Chromium instalado dentro de Lerd. Este comando **no fue el utilizado originalmente** para instalar Puppeteer en `base` (no quedó registrado cómo se instaló allí); es una recomendación basada en la documentación oficial de Puppeteer.

### Paso 4.5 — Comprobar la dependencia (ejecutado con éxito en `base`)

```bash
npm ls puppeteer --depth=0
```

En el proyecto probado se obtuvo `puppeteer@25.16.0`. La versión de un nuevo proyecto puede variar.

### Paso 4.6 — Confirmar que el contenedor encuentra Puppeteer (recomendado)

Sustituye el directorio real y el contenedor si cambia la versión PHP:

```bash
podman exec --workdir /home/USUARIO/Proyectos/NOMBRE_PROYECTO lerd-php84-fpm node -p "require.resolve('puppeteer')"
```

El comando **equivalente que sí funcionó** en el proyecto original fue:

```bash
podman exec --workdir /home/ro/Proyectos/base lerd-php84-fpm node -p "require.resolve('puppeteer')"
```

Y produjo:

```text
/home/ro/Proyectos/base/node_modules/puppeteer/lib/puppeteer/puppeteer.js
```

**Advertencia:** ejecutar `require.resolve('puppeteer')` desde `/var/www/html` dio `MODULE_NOT_FOUND` porque no era el directorio del proyecto. No hubo que reinstalar Puppeteer: bastó utilizar el `--workdir` correcto.

## 5. Configuración persistente por proyecto (recomendada, todavía no probada en esta conversación)

Las pruebas exitosas utilizaron `withBrowsershot(...)` **por documento**. Para no repetir esas opciones en todo el código, Spatie permite configuración por variables de entorno.

### Paso 5.1 — Respaldar `.env` antes de modificarlo (recomendado)

```bash
cp -a .env .env.bak.pre-spatie-pdf
```

No sustituyas un respaldo previo sin revisarlo. No publiques `.env` ni su copia en Git.

### Paso 5.2 — Configurar `.env`

Incorpora estas claves al `.env` del **nuevo proyecto**, respetando las existentes y sin duplicarlas:

```dotenv
LARAVEL_PDF_DRIVER=browsershot
LARAVEL_PDF_CHROME_PATH=RUTA_REAL_DE_CHROMIUM_EN_LERD
LARAVEL_PDF_NODE_MODULES_PATH=/home/USUARIO/Proyectos/NOMBRE_PROYECTO/node_modules
LARAVEL_PDF_NO_SANDBOX=true
```

- **Cambia los marcadores** `RUTA_REAL_DE_CHROMIUM_EN_LERD`, `USUARIO` y `NOMBRE_PROYECTO` por valores comprobados.
- `LARAVEL_PDF_NO_SANDBOX=true` reproduce la opción `noSandbox()` que funcionó dentro del contenedor probado. **Desactiva una protección de Chromium**; limítala al entorno de Lerd donde sea estrictamente necesaria y no la adoptes por defecto en producción.
- En el proyecto probado no fue necesario establecer `LARAVEL_PDF_NODE_BINARY` ni `LARAVEL_PDF_NPM_BINARY`; Node.js ya estaba en el entorno del contenedor.
- Spatie lee estas variables desde su configuración de paquete. **Publicar `config/laravel-pdf.php` es opcional**, salvo que quieras personalizarlo más.

### Paso 5.3 — Publicar la configuración si necesitas editar valores avanzados (opcional)

```bash
lerd artisan vendor:publish --tag=pdf-config
```

El archivo generado será `config/laravel-pdf.php`. Si ya existe, respáldalo y revisa diferencias **antes** de sobrescribirlo. Una opción adicional para eliminar la ruta absoluta del `node_modules` es usar en ese archivo `base_path('node_modules')` como valor por defecto de `node_modules_path`. Esta personalización es **recomendada, no validada todavía** en el proyecto `base`.

### Paso 5.4 — Limpiar configuración en caché, solo si los cambios no se reflejan (recomendado)

```bash
lerd artisan config:clear
```

Este paso es innecesario cuando el proyecto no tiene configuración cacheada.

## 6. Pruebas de funcionamiento

### Prueba A — PDF desde Laravel con parámetros explícitos (ejecutada y exitosa en `base`)

Este es el **comando exacto que funcionó** en nuestro entorno. Genera un PDF temporal **dentro del contenedor**:

```bash
podman exec --workdir /home/ro/Proyectos/base lerd-php84-fpm php artisan tinker --execute='\Spatie\LaravelPdf\Facades\Pdf::html("<h1>PDF LARAVEL OK</h1>")->withBrowsershot(fn (\Spatie\Browsershot\Browsershot $browser) => $browser->setNodeModulePath("/home/ro/Proyectos/base/node_modules")->setChromePath(trim(shell_exec("command -v chromium")))->noSandbox())->save("/tmp/lerd-laravel-pdf-test.pdf");'
```

A continuación se verificó con este **otro comando**, ejecutado exitosamente:

```bash
podman exec lerd-php84-fpm file /tmp/lerd-laravel-pdf-test.pdf
```

Resultado real:

```text
/tmp/lerd-laravel-pdf-test.pdf: PDF document, version 1.4, 1 page(s)
```

### Prueba B — Versión reutilizable para otro proyecto, sin rutas fijas (recomendada, no ejecutada)

Desde el directorio raíz del nuevo proyecto, esta prueba utiliza `base_path('node_modules')` y busca Chromium dentro del contenedor; guarda un PDF de prueba en el directorio compartido del proyecto:

```bash
lerd artisan tinker --execute='\Spatie\LaravelPdf\Facades\Pdf::html("<h1>SPATIE PDF OK</h1>")->withBrowsershot(fn (\Spatie\Browsershot\Browsershot $browser) => $browser->setNodeModulePath(base_path("node_modules"))->setChromePath(trim(shell_exec("command -v chromium")))->noSandbox())->save(storage_path("app/spatie-pdf-prueba.pdf"));'
```

**Verificación del archivo:**

```bash
file storage/app/spatie-pdf-prueba.pdf
```

Debería indicar `PDF document`, con una o más páginas. Esta prueba modifica **solo** el archivo de prueba en `storage/app/`.

### Prueba C — Comprobar la configuración persistente (recomendada, aún no validada)

Después de completar la sección 5, desde el proyecto:

```bash
lerd artisan tinker --execute='\Spatie\LaravelPdf\Facades\Pdf::html("<h1>CONFIGURACION SPATIE OK</h1>")->save(storage_path("app/spatie-pdf-config-test.pdf"));'
```

Si genera un PDF válido **sin `withBrowsershot()`**, queda demostrado que las opciones globales de la aplicación están bien configuradas. Esta verificación es independiente de la prueba A.

## 7. Uso en una aplicación real

Ejemplo PHP para un controlador, servicio o acción Laravel:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.factura', ['factura' => $factura])
    ->format('a4')
    ->save(storage_path('app/factura.pdf'));
```

La vista correspondiente sería `resources/views/pdf/factura.blade.php`. Ese ejemplo ilustra la API documentada de Spatie, **no fue ejecutado** durante las pruebas. Los estilos, imágenes y recursos deben ser accesibles para Chromium; no desactives indiscriminadamente la seguridad del navegador para resolver recursos bloqueados.

## 8. Diagnóstico de fallos habituales

| Síntoma | Interpretación / siguiente comprobación |
|---|---|
| `Cannot find module 'puppeteer'` | Comprueba que `npm install` se realizó en el proyecto y que Node.js resuelve el módulo desde el directorio correcto; fue el caso en `base`. |
| `Chrome/Chromium executable not found` | Verifica la ruta **dentro del contenedor**, no en Linux Mint. |
| `No usable sandbox` | El contenedor podría exigir `noSandbox()`; evalúa el riesgo antes de usarlo. |
| Mensajes DBus, EGL o Vulkan | Aparecieron en las pruebas, pero **no impidieron** generar PDFs. Si la salida es válida, no modifiques controladores gráficos por estas advertencias aisladas. |
| `failed to extract ... Connection aborted / unexpected end of file` | Puede deberse a una transferencia o extracción interrumpida. En la experiencia documentada, un segundo intento concluyó correctamente; causa inicial no demostrada. |
| PDF sin CSS, fuentes o imágenes | Verifica URL de recursos, acceso a archivos y carga de estilos antes de cambiar opciones de seguridad. |

## 9. Reversión y mantenimiento

- **Proyecto:** antes de revertir dependencias, verifica si otra parte de la aplicación utiliza Spatie PDF o Puppeteer. Los cambios de `composer require` y `npm install` afectan los manifiestos y archivos de bloqueo del proyecto; reviértelos de forma controlada desde Git o mediante los gestores de paquetes.
- **`.env`:** conserva el respaldo; compara sus valores con el archivo actual antes de restaurar, para no perder cambios posteriores.
- **PDF de prueba:** puede borrarse cuando dejes de necesitarlo; no es una dependencia del sistema.
- **Chromium en Lerd:** `lerd php:pkg remove chromium` es una reversión posible, **pero afecta a las versiones PHP gestionadas por Lerd**; no lo ejecutes si otro proyecto todavía necesita Chromium.
- **Seguridad:** `noSandbox()` debe considerarse una excepción del contenedor de desarrollo, no una configuración general para producción.
- **Persistencia:** Lerd conserva la declaración del paquete Chromium en su configuración y la reaplica al reconstruir imágenes; Puppeteer y las dependencias Composer se administran por proyecto.

## 10. Lista de comprobación para dar por finalizada una nueva instalación

- [ ] PHP del proyecto es compatible con Laravel PDF.
- [ ] Chromium aparece entre los paquetes de la imagen PHP correspondiente.
- [ ] Chromium inicia dentro del contenedor Lerd.
- [ ] Node.js funciona dentro del contenedor.
- [ ] `spatie/laravel-pdf` y `spatie/browsershot` están instalados en el proyecto.
- [ ] Puppeteer está instalado y accesible desde **el directorio del proyecto** dentro de Lerd.
- [ ] Se obtuvo la ruta real de Chromium.
- [ ] Se generó y validó un PDF mediante la integración Laravel–Spatie.
- [ ] Si se desea configuración persistente: la prueba **sin `withBrowsershot()`** funcionó.
- [ ] Se revisaron los posibles riesgos de `noSandbox()` y se guardó respaldo de cualquier archivo modificado.

## 11. Documentación oficial consultada

- Spatie Laravel PDF v2 — instalación: https://spatie.be/docs/laravel-pdf/v2/installation-setup
- Spatie Laravel PDF v2 — requisitos: https://spatie.be/docs/laravel-pdf/v2/requirements
- Spatie Laravel PDF v2 — configuración: https://spatie.be/docs/laravel-pdf/v2/drivers/configuration
- Spatie Laravel PDF v2 — personalización de Browsershot: https://spatie.be/docs/laravel-pdf/v2/drivers/customizing-browsershot
- Spatie Laravel PDF v2 — creación de PDFs: https://spatie.be/docs/laravel-pdf/v2/basic-usage/creating-pdfs
- Spatie Browsershot — requisitos: https://spatie.be/docs/browsershot/v4/requirements
- Puppeteer — configuración: https://pptr.dev/guides/configuration
- Puppeteer — instalación: https://pptr.dev/guides/installation
- Lerd — PHP: https://lerd.sh/usage/php

**Nota de alcance:** esta guía documenta el comportamiento **en el entorno local Lerd probado**. En producción, CI, otro PHP o diferente imagen/contenedor, vuelve a verificar rutas, dependencias, permisos, seguridad y disponibilidad del navegador; no asumas que son idénticos.
