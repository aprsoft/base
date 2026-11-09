# Timbre PDF417 para DTE chileno — Laravel 12 / PHP 8.4

## Instalación

```bash
composer require tecnickcom/tc-lib-barcode
php -m | grep -Ei 'gd|imagick|dom'
```

Si faltan `gd` o `imagick`, habilítala en la versión de PHP que utiliza el proyecto.

Copia los archivos del directorio `app/Services/Dte` en las mismas rutas de tu proyecto Laravel.

## Uso con tu respuesta de API (XML codificado Base64)

```php
use App\Services\Dte\TimbrePdf417Service;
use Illuminate\Support\Facades\Storage;

// $response['xml'] puede ser Base64; $document->id es un identificador interno.
$png = app(TimbrePdf417Service::class)->generar($response['xml']);
Storage::disk('local')->put('timbres/dte-' . $document->id . '.png', $png);
```

## Uso con XML sin codificar

```php
$xml = file_get_contents('/ruta/al/dte.xml');
$png = app(TimbrePdf417Service::class)->generar($xml);
file_put_contents('/ruta/al/timbre.png', $png);
```

## Devolver la imagen desde un controlador

```php
return response($png, 200)->header('Content-Type', 'image/png');
```

## Precauciones

- El XML debe contener un TED que ya esté firmado (DD + CAF + FRMT).
- El timbre se genera usando **únicamente los bytes originales del TED**.
- La imagen es PDF417 completo, compactación binaria forzada y ECL 5.
- La resolución de trabajo prevista es 600 dpi, con 5 píxeles por módulo X y 150 px de margen blanco a cada lado. **La biblioteca no necesariamente graba los metadatos DPI del PNG**: al imprimir, configurar las dimensiones físicas según los píxeles / 600.
- No reducir el ancho hasta que el módulo X mida menos de 6,7 mil (0,17018 mm).
- El SII recomienda código de barras de hasta 9 × 3 cm, pero para TED extensos puede ser físicamente imposible conservar simultáneamente esa medida y el ancho mínimo de módulo; no comprimir el código de forma ilegible para forzarlo.
- Este módulo **no valida la firma FRMT**, **no verifica la autenticidad del CAF ante SII**, ni comprueba que el DTE esté aceptado. Genera la representación gráfica de los datos recibidos.

Fuente: [Instructivo técnico SII, Anexo 2, A.2.5](https://www.sii.cl/servicios_online/docs/instructivo_emision.pdf).
