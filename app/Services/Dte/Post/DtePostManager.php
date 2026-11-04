<?php

namespace App\Services\Dte\Post;

use App\Models\Company;
use App\Models\Document;
use App\Models\ErrorDte;
use App\Services\Dte\DteConexion;
use App\Services\Dte\DteResponse as DteResponse;
use App\Services\Dte\Post\DtePostApi;
use Throwable;

class DtePostManager
{    
    // throw new \Exception('Fallé a propósito');

    public Company $company;

    public function __construct()
    {
        $this->company = Company::query()->firstOrFail();
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
            $response = DtePostApi::curl($payload, $this->company,$document->id);
            

            if (isset($response['WARNING']) ) {

                ErrorDte::create([
                    'document_id' => $document->id,
                    'warning'     => json_encode($response['WARNING']),
                    
                ]);

               

            }else {

            ErrorDte::create([
                    'document_id' => $document->id,
                    'error'     => json_encode($response),
                    
                ]);


            }
          

            // if ($response === false) {
            //     ErrorLog::create([
            //         'message'   => curl_error($curl),
            //         'exception' => 'CurlError',
            //     ]);
            // }

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

        } catch (Throwable $e) {
            
            throw $e;           
        }
    }

    
}


