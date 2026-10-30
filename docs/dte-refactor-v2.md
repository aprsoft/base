# DTE V2 - integración progresiva con OpenFactura

**Estado:** propuesta implementada en paralelo al módulo legado. No conectada todavía a rutas ni modelos de BASE; no se han realizado emisiones reales.

## Alcance
- Transporte Laravel HTTP único, desarrollo y producción en hosts oficiales fijos.
- Emisión POST /v2/dte/document y consulta GET /v2/dte/document/{rut}/{tipo}/{folio}/{formato}.
- La API key se pasa explícitamente por operación: no se consulta Company::first(), para evitar seleccionar accidentalmente otra empresa o tenant.
- La clave de idempotencia se pasa explícitamente y NO se renueva en reintentos de la misma operación.
- No hay reintentos automáticos de emisiones. Un timeout o HTTP 5xx es indeterminado, no prueba de rechazo.
- Validación estructural mínima de DTE 33, 34, 39, 41, 52, 56 y 61, sin inventar campos ni calcular tributos.
- El resultado del proveedor no equivale a aceptación definitiva del SII.
- El código original de app/Services/Dte sigue sin modificarse.

## Uso desde una capa de aplicación ya autorizada

~~~php
use App\Services\Dte\V2\DteService;

// $dte proviene de un mapeador verificado conforme al tipo documental.
// $apiKey pertenece a la empresa/tenant autorizado.
// $operationKey es persistente y único por empresa y operación.
$result = app(DteService::class)->emit(
    dte: $dte,
    apiKey: $apiKey,
    idempotencyKey: $operationKey,
    environment: 'development',
    resources: ['FOLIO', 'XML', 'PDF'],
);

if ($result->requiresReconciliation()) {
    // Consultar estado antes de reintentar.
}

// No enviar $result->data a logs ni respuestas públicas sin autorización.
~~~

## Trabajos pendientes antes de producción
1. Incorporar/verificar modelos, tablas, conexión central o tenant, control de permisos y pertenencia del DTE a empresa.
2. Implementar mappers desde el modelo de negocio real para cada tipo; evitar valores tributarios fijos.
3. Persistir idempotency key, TOKEN, folio, solicitud/resultado y estado con unicidad por empresa. Diseñar transiciones e impedir doble emisión concurrente.
4. Guardar XML de forma privada, cifrada/protegida y accesible según obligaciones SII; resguardar PDF.
5. Completar validación tributaria específica, referencias, descuentos, redondeos, guías, boletas y medios de pago; validar contra SII y OpenFactura.
6. Confirmar secuencia de estados mediante consulta al proveedor; probar con claves y empresas de desarrollo, nunca con datos reales para pruebas.
7. Ejecutar suite Pest en Lerd; la revisión estática no equivale a pruebas ejecutadas.

Documentación:
- https://docsapi-openfactura.haulmer.com/
- https://www.sii.cl/servicios_online/1039-descripcion_formato-1184.html
- https://laravel.com/docs/12.x/http-client

## Reversión
Se creó la rama de respaldo backup/dte-before-refactor-20261030-0801cc7
desde HEAD 0801cc7c9528e1a0fb48ea4f5bbebceba56fcc71.
Los archivos del módulo legado permanecen intactos.
