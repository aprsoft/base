# Corrección de mappers DTE — BASE, `serviceDte`

Código verificado contra HEAD `5520c6fb25684807c2cbc3b78155d4b7625c49e3`.

**Alcance**: exclusivamente los siete archivos de `app/Services/Dte/Post/Mapper/`.
No modifica modelos, migraciones, controllers, rutas, factories, servicios HTTP ni datos.

## Correspondencias confirmadas en la migración de details

- `cantidad` => `QtyItem`
- `valor` para precio unitario => `PrcItem` o `PrcNetoItem` para exentos
- `cantidad * valor` => columna `MontoItem` o `MontoNetoItem` para exentos
- Monto asociado a registros de descuento o informativos => `MontoItem` (columna física; convención fiscal PENDIENTE de verificar con registros reales).
- `details.nroLinDet` existe, pero el mapeo establece secuencias desde 1 para las líneas vigentes filtradas.
- `details.IndExe` existe; se incluye solo cuando tiene valor, y 1 en documentos exentos.

## Correcciones compatibles con el manager actual

- Uniformado método `mapJsonDte()` sin argumentos en seis mappers.
- Código 56 para `NotaDebito`, 61 para `NotaCredito`.
- `Referencia` de notas se toma de `parent()` y el `CodRef` de `documents.CodRef`; no se asume CodRef=1.
- `documents.TpoTranCompra`, `TpoTranVenta` y `fmaPago` se usan cuando existen para facturas.
- `FacturaExenta` usa `$document->exento`, no `$document->neto`.
- Se evita que el servicio 10 (descuento) entre simultáneamente en `Detalle` y `DscRcgGlobal`.
- `GuiaDespacho` usa `CdgSIISucur` y las relaciones confirmadas; no `references`, `transporte` ni `aduana` no verificadas.

## PENDIENTE CRÍTICO: guía de despacho

El campo tributario `IndTraslado` NO existe en la migración `documents`. El método
`GuiaDespacho::mapJsonDte(?int $indTraslado = null)` recibe este dato expresamente,
pero el manager actual llama `mapJsonDte()` sin argumento, por lo que la guía queda
BLOQUEADA hasta que se defina la fuente real del indicador. Se impide emitir una
operación fiscal con un valor supuesto. Los campos de transporte, destino y aduana
tampoco están respaldados por las migraciones actuales. Verificar normativa SII vigente.

## PENDIENTE: semántica de montos

`MontoItem` es una columna física de `details`, pero no hay evidencia de que los
registros `service_id=10,9,11` carguen en ella respectivamente el valor fiscal
de `ValorDR` o `SubTotExeSTI`; validar con datos reales antes de emitir.

Los totales se obtienen de los accessors actuales de `Document`; pueden incluir
registros `NO VIGENTE` y/o descuentos en sus sumatorias, por lo que deben
contrastarse con el DTE antes de activar producción.

`Company::query()->firstOrFail()` se conserva para no ampliar alcance, pero no
demuestra aislamiento entre emisores si hay más de una empresa.

Las notas de crédito/débito mantienen el cálculo original de IVA, apropiado solo
si corresponde a su operación. No se valida aquí todo el contrato fiscal del SII.

**Pruebas**: sintaxis PHP validada localmente; no se ha ejecutado Laravel/Pest,
ni una emisión HTTP/tributaria.
