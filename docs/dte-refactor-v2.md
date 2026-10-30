# Refactor DTE V2 — BASE / OpenFactura

**Código creado en:** app/Services/Dte/V2.
**Respaldo íntegro:** backups/dte-legacy-20261030 (árbol Git original, sin alteraciones);
rama backup/dte-before-refactor-20261030-0801cc7.

## Alcance real

- Emisión JSON vía POST /v2/dte/document; consultado conforme a la documentación oficial.
- GET /v2/dte/document/{rut}/{type}/{folio}/{value}; formatos status/xml/json/pdf/cedible.
- POST /v2/dte/document/issued y /received con filtros paginados.
- GET /v2/dte/organization.
- Claves de API por operación, ambientes permitidos en código (desarrollo/producción).
- Cabecera Idempotency-Key: el integrador debe guardar una clave por operación, reusarla y garantizar unicidad por empresa.
- Sin reintentos automáticos de POST. Timeouts, 5xx y OF-06 se clasifican como desconocidos y requieren conciliación.
- Separación de los 7 tipos 33,34,39,41,52,56,61 bajo una interfaz DteMapper.
- Los mappers reciben arrays con nomenclatura OpenFactura/SII y mantienen sus valores intactos.
- Validador estructural mínimo. No sustituye validación tributaria, ni comprueba RUT por dígito verificador, montos versus líneas, reglas de referencias, redondeos, fecha tributaria o nuevos campos de guía.
- DteResult indica estado HTTP/proveedor; **emitted nunca implica aceptación definitiva del SII**.
- Ninguna operación de base de datos, modificación de rutas ni emisión real.

## Limitaciones para integrar a BASE

En el HEAD inspeccionado no existen App\Models\Document ni App\Models\Company,
ni tablas de documentos, detalles, relaciones o emisores. El legado depende de
esos modelos, por lo que no se puede crear un adaptador Eloquent confiable sin
verificar el esquema real. La V2 es un servicio de API utilizable con arrays ya
construidos, pero no un flujo de facturación persistente de extremo a extremo.
No se registra ni se envía ningún dato tributario real sin autorización.

El llamador debe:
1. Autorizar empresa/tenant, conexión, documento y operación de emisión.
2. Construir y validar el DTE real con datos pertenecientes al emisor.
3. Guardar de forma transaccional la clave idempotente, hash de payload y
   estado de emisión antes de cualquier llamada; impedir emisiones simultáneas.
4. Guardar TOKEN, folio, XML, PDF y estado en almacenamiento privado según
   autorizaciones/normativa. Tratar respuestas con outcome "unknown" mediante
   conciliación, no regenerando claves.
5. Consultar "status" hasta obtener el estado del SII y registrar sus cambios.
6. Ejecutar pruebas en el ambiente de desarrollo y verificar códigos tributarios
   de cada DTE antes de activar en producción.

~~~php
use App\Services\Dte\V2\DteService;

// EJEMPLO CONCEPTUAL: los tres valores deben provenir del contexto
// autorizado; NO son modelos/rutas confirmados en BASE.
$result = app(DteService::class)->emit(
    dte: $dte,
    apiKey: $apiKey,
    idempotencyKey: $stableOperationKey,
    environment: 'development',
    resources: ['FOLIO', 'XML', 'PDF'],
);

if ($result->requiresReconciliation()) {
    // No volver a emitir sin consultar al proveedor.
}
~~~

## Pruebas

Archivos tests/Unit/Dte/DteDocumentValidatorTest.php y DteServiceTest.php.
Emplean Http::fake y preventStrayRequests para evitar emisiones por accidente.
**No se ha ejecutado Pest ni probado el proveedor remoto.**

## Documentación consultada
- https://docsapi-openfactura.haulmer.com/
- https://www.sii.cl/
- https://laravel.com/docs/12.x/http-client

## Reversión
El código legado no fue modificado. Para eliminar la V2 sería suficiente
revertir sus commits sin modificar tablas (no se agregaron migraciones).
