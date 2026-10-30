<?php

namespace App\Services\Dte\Get;

use App\Models\Company;
use App\Models\Document;
use App\Models\ErrorLog;
use App\Services\Dte\DteConexion;
use App\Services\Dte\DteResponse;
use Illuminate\Database\Eloquent\Collection;

class DteGetManager
{
    public Company $company;

    public function __construct()
    {
        $this->company = Company::query()->firstOrFail();
    }

    public function getDocument(Document $document, string $formato = 'json'): DteResponse
    {
        $apiUrl = $this->company->url_document_received;

        if (!DteConexion::hasConexion($apiUrl)) {
            return new DteResponse(
                accepted: false,
                message: 'No hay conexión a internet o el proveedor DTE no responde',
                errors: []
            );
        }

        $url = sprintf(
            '%s/%s/%s/%s/%s',
            rtrim($this->company->url, '/'),
            $this->company->rut_emisor,
            $document->typedocument->codigo,
            $document->folio,
            $formato
        );

        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => [
                    'apikey: ' . $this->company->apikey,
                    'Content-Type: application/json',
                ],
            ]);

            $rawResponse = curl_exec($curl);
          dd($rawResponse);

            if ($rawResponse === false) {
                ErrorLog::create([
                    'message'   => curl_error($curl),
                    'exception' => 'CurlError',
                ]);
            }

            if (empty($rawResponse)) {
                return new DteResponse(
                    accepted: false,
                    message: 'Respuesta vacía del proveedor DTE',
                );
            }

            $response = json_decode($rawResponse, true);    


            if (isset($response['folio'])) {
                return new DteResponse(
                    accepted: true,
                    data: $response,
                    pdf: $response['pdf']?? null
                );
            }

            if (isset($response['error'])) {
                return new DteResponse(
                    accepted: false,
                    message: $response['error']['message'] ?? 'SIN INFORMACION',
                    errors: $response
                );
            }

            return new DteResponse(
                accepted: false,
                message: 'Respuesta inválida del proveedor DTE',
                errors: $response ?? []
            );

        } catch (\Throwable $e) {

            ErrorLog::create([
                'document_id' => $document->id,
                'folio'       => $document->folio,
                'tipo_dte'    => $document->typedocument->codigo,
                'rut_emisor'  => $this->company->rut_emisor,
                'message'     => $e->getMessage(),
                'exception'   => get_class($e),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            throw $e;
        }
    }


    public function getDocumentsSupplier($pagina,$fecha): DteResponse
    {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL =>$this->company->url_document_received,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		// CURLOPT_POSTFIELDS =>'{"Page":"1","TipoDTE":{"eq":"33"},"FchEmis":{"gte":"2020-05-13"},"RUTEmisor":{"eq":"76430498"}}',
		// CURLOPT_POSTFIELDS =>'{"Page":"1","TipoDTE":{"eq":"33"},"FchEmis":{"eq":"2020-05-13"}}',
		// CURLOPT_POSTFIELDS =>'{"Page":"'.$pagina.'","TipoDTE":{"eq":"'.$tipo_documento.'"},"FchEmis":{"gte":"'.$fecha.'"}}',
		// CURLOPT_POSTFIELDS =>'{"Page":"'.$pagina.'","TipoDTE":{"eq":"'.$tipo_documento.'"}}',
		// CURLOPT_POSTFIELDS =>'{"Page":"'.$pagina.'","FchEmis":{"gte":"'.$fecha.'"}}',
        CURLOPT_POSTFIELDS =>'{"Page":"'.$pagina.'","FchEmis":{"gte":"'.$fecha.'"}}',
        CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->company->apikey,                
            ],
		
		));

		$rawResponse = curl_exec($curl);        
        

        if ($rawResponse === false) {
            ErrorLog::create([
                'message'   => curl_error($curl),
                'exception' => 'CurlError',
            ]);
        }

        if (empty($rawResponse)) {
            return new DteResponse(
                accepted: false,
                message: 'Respuesta vacía del proveedor DTE',
            );
        }

        $response = json_decode($rawResponse, true); 

        if ( isset($response['statusCode']) || isset($response['message']) ) {
            
            return new DteResponse(
                accepted: False,
                message: $response['message']?? null,
                statusCode: $response['statusCode']?? null, 
                errors: $response                 
            );
        }
        
        return new DteResponse(
                accepted: True,
                current_page: $response['current_page'],
                last_page: $response['last_page'] ,
                data: $response['data'] ,
                total: $response['total']              
            );		
    }

    public function getDetailsSupplier(
        string $rut,
        int $tipoDocumento,
        int $folio,
        string $formato = 'json'
    ): DteResponse {

        $baseUrl = rtrim($this->company->url_document_detail, '/');

        if (!DteConexion::hasConexion($baseUrl)) {
            return new DteResponse(
                accepted: false,
                message: 'No hay conexión a internet o el proveedor DTE no responde',
                errors: []
            );
        }

        $url = sprintf(
            '%s/%s/%s/%s/%s',
            $baseUrl,
            $rut,
            $tipoDocumento,
            $folio,
            $formato
        );

        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => [
                    'apikey: ' . $this->company->apikey,
                    'Content-Type: application/json',
                ],
            ]);

            $rawResponse = curl_exec($curl);

            if ($rawResponse === false) {
                ErrorLog::create([
                    'message'   => curl_error($curl),
                    'exception' => 'CurlError',
                ]);
            }

            if (empty($rawResponse)) {
                return new DteResponse(
                    accepted: false,
                    message: 'Respuesta vacía del proveedor DTE',
                );
            }

            $response = json_decode($rawResponse, true);

            if (isset($response['statusCode'])) {
                return new DteResponse(
                    accepted: false,
                    message: $response['message'] ?? 'Error proveedor DTE',
                    statusCode: $response['statusCode']
                );
            }

            if (isset($response['json'])) {
                return new DteResponse(
                    accepted: true,
                    data: $response['json']
                );
            }

            return new DteResponse(
                accepted: false,
                message: 'Respuesta inválida del proveedor DTE',
                errors: $response ?? []
            );

        } catch (\Throwable $e) {

            ErrorLog::create([            
                'message'    => $e->getMessage(),
                'exception'  => get_class($e),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            throw $e;
        }
    }    
}
