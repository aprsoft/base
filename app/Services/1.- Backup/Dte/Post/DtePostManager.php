<?php

namespace App\Services\Dte\Post;

use App\Models\Company;
use App\Models\Document;
use App\Models\ErrorLog;
use App\Services\Dte\DteConexion;
use App\Services\Dte\DteResponse as DteResponse;


class DtePostManager
{    
    // throw new \Exception('Fallé a propósito');

    public Company $company;

    public function __construct()
    {
        $this->company = Company::query()->firstOrFail();
        // dd($this->company->url);
    }

    public function post(Document $document)
    { 
        
        try {          
        
            $apiUrl = $this->company->url;

            if (!DteConexion::hasConexion($apiUrl)) {
                return new DteResponse(
                    accepted: false,
                    message: 'No hay conexión a internet o el proveedor DTE no responde',
                    errors: []
                );
            }

            $payload = DteFactory::resolve($document)->mapJsonDte();    

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $this->company->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    "apikey:".$this->company->apikey,
                    "Idempotency-Key:".$document->id,
                    "Content-Type: application/json",
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

            if (isset($response['FOLIO'])) {
                return new DteResponse(
                    accepted: true,
                    folio: $response['FOLIO'],                    
                    warnings: $response['WARNING'] ?? [],
                    errors: $response
                );
            }

            return new DteResponse(
                accepted: false,
                document_id: $document->id,
                message: $response['error']['message']
                    ?? $response['message']
                    ?? 'Error desconocido',
                errors: $response ?? [],
                
            );

        } catch (\Throwable $e) {
            
            throw $e;           
        }
    }

    
}


