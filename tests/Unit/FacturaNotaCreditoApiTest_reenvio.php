<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Typedocument;
use App\Services\Document\serviceFacturaAfecta;
use App\Services\Document\serviceNotaCredito;
use App\Services\Dte\Post\Classes\DteApiFactory;
use App\Services\Dte\Post\Classes\DteApiSend;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Un único test de integración, sin alterar las clases de la aplicación.
// Ubicar en tests/Unit: tests/Pest.php aplica RefreshDatabase a Feature,
// lo que impediría conservar en MySQL los documentos aceptados.
uses(TestCase::class);

test('API desarrollo: factura afecta 33 y su nota de crédito 61 se persisten solo si fueron aceptadas', function (): void {
    if ((string) env('OPENFACTURA_RUN_LIVE_TESTS', '0') !== '1') {
        $this->markTestSkipped('Habilitar emisiones reales con OPENFACTURA_RUN_LIVE_TESTS=1.');
    }

    if (! app()->environment('testing')) {
        throw new LogicException('Solo se permite ejecutar este test con APP_ENV=testing.');
    }

    // phpunit.xml utiliza SQLite en memoria. Para este test se apunta
    // explícitamente a una base MySQL AISLADA y previamente migrada/sembrada.
    // No cambia la configuración persistente ni el phpunit.xml del proyecto.
    $mysql = config('database.connections.mysql');
    if (! is_array($mysql) || ($mysql['driver'] ?? null) !== 'mysql') {
        throw new LogicException('No existe una conexión MySQL configurada.');
    }

    $mysql['database'] = 'base_api_testing';
    config()->set('database.connections.openfactura_testing', $mysql);
    config()->set('database.default', 'openfactura_testing');
    DB::purge('openfactura_testing');

    if (DB::connection()->getDatabaseName() !== 'base_api_testing') {
        throw new LogicException('Se bloqueó el uso de una base diferente de base_api_testing.');
    }

    $company = Company::query()->firstOrFail();
    if ((string) $company->url !== 'https://dev-api.haulmer.com/v2/dte/document') {
        throw new LogicException('Se bloqueó el envío fuera de la API de desarrollo.');
    }
    if ((string) $company->rut_emisor !== '76430498-5') {
        throw new LogicException('El emisor configurado debe ser HOSTY SPA (76430498-5).');
    }
    if (blank($company->apikey)) {
        throw new LogicException('Falta la API key de desarrollo de HOSTY en companies.');
    }

    // serviceFacturaAfecta utiliza customer_id=1 y user_id=1.
    // El cliente 1 NO puede ser HOSTY, pues HOSTY es el emisor.
    $customer = Customer::query()->findOrFail(1);
    if ((string) $customer->rutDte === (string) $company->rut_emisor) {
        throw new LogicException('El receptor coincide con el RUT del emisor HOSTY.');
    }
    if (! DB::table('users')->where('id', 1)->exists()
        || ! DB::table('services')->where('id', 6)->exists()
        || (int) Typedocument::query()->findOrFail(15)->codigo !== 33
        || (int) Typedocument::query()->findOrFail(13)->codigo !== 61) {
        throw new LogicException('Faltan datos necesarios en base_api_testing.');
    }

    /**
     * Crea, envía y confirma UN documento.
     * Si la API responde con error o no confirma TOKEN/FOLIO, se revierte
     * SOLO este documento, conservando los previamente aceptados.
     * WARNING no se considera rechazo.
     */
    $emitir = function (callable $crear, int $tipoDte): array {
        DB::beginTransaction();
        $document = null;

        try {
            $document = $crear();
            $json = DteApiFactory::resolve($document)->mapJsonDte();
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            expect((int) data_get($payload, 'dte.Encabezado.IdDoc.TipoDTE'))->toBe($tipoDte);
            expect($document->details()->count())->toBeGreaterThan(0);

            // Servicio HTTP real de la aplicación; no se utiliza Http::fake().
            $api = DteApiSend::send($json, (int) $document->id);

            if (! is_array($api)) {
                throw new RuntimeException('Respuesta inexistente o inválida de OpenFactura. Estado remoto no confirmado.');
            }

            if (isset($api['error'])) {
                throw new RuntimeException('OpenFactura rechazó el DTE: '.json_encode($api['error'], JSON_UNESCAPED_UNICODE));
            }

            // Documentación: TOKEN identifica una emisión exitosa, y se pidió FOLIO.
            // WARNING puede venir junto con una respuesta aceptada.
            if (empty($api['TOKEN']) || ! isset($api['FOLIO']) || (int) $api['FOLIO'] <= 0) {
                throw new RuntimeException('La respuesta no confirma TOKEN y FOLIO: '.json_encode(array_keys($api)));
            }

            $document->folio = (int) $api['FOLIO'];
            $document->save();

            DB::commit();

            return [$document->fresh(), $api, $payload];
        } catch (Throwable $e) {
            DB::rollBack();

            if ($document !== null && (
                Document::query()->whereKey($document->id)->exists()
                || DB::table('details')->where('document_id', $document->id)->exists()
            )) {
                throw new RuntimeException('Falló el rollback del documento rechazado.', previous: $e);
            }

            throw $e;
        }
    };

    // 1. Emite la factura. Si se acepta (aunque tenga WARNING), queda guardada.
    [$factura, $respuestaFactura] = $emitir(
        fn (): Document => (new serviceFacturaAfecta())->createDocument(15),
        33
    );

    expect((int) $factura->folio)->toBeGreaterThan(0);
    expect(Document::query()->whereKey($factura->id)->exists())->toBeTrue();

    // 2. Verifica que la nota pueda reflejar correctamente la factura.
    //    La comprobación se hace DENTRO de la transacción de la nota para
    //    que cualquier fallo revierta la nota sin borrar la factura aceptada.
    [$nota, $respuestaNota, $payloadNota] = $emitir(
        function () use ($factura): Document {
            $nota = (new serviceNotaCredito())->createDocument($factura);

            expect((int) $nota->parent_id)->toBe((int) $factura->id);
            expect((int) $nota->neto)->toBe((int) $factura->neto);
            expect((int) $nota->total)->toBe((int) $factura->total);

            return $nota;
        },
        61
    );

    expect((int) $nota->parent_id)->toBe((int) $factura->id);
    expect((int) $nota->folio)->toBeGreaterThan(0);
    expect((int) data_get($payloadNota, 'dte.Referencia.0.TpoDocRef'))->toBe(33);
    expect((int) data_get($payloadNota, 'dte.Referencia.0.FolioRef'))->toBe((int) $factura->folio);
    expect((int) data_get($payloadNota, 'dte.Referencia.0.CodRef'))->toBe(1);
    expect(Document::query()->whereKey($factura->id)->exists())->toBeTrue();
    expect(Document::query()->whereKey($nota->id)->exists())->toBeTrue();

    // Un WARNING no invalida ninguna de las dos emisiones.
    foreach ([$respuestaFactura, $respuestaNota] as $respuesta) {
        if (array_key_exists('WARNING', $respuesta)) {
            expect($respuesta['TOKEN'])->not->toBeEmpty();
            expect((int) $respuesta['FOLIO'])->toBeGreaterThan(0);
        }
    }
});
