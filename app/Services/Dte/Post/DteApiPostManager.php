<?php

namespace App\Services\Dte\Post;

use App\Models\Company;
use App\Models\Document;
use App\Services\Dte\Common\DteApiConexion;
use App\Services\Dte\Common\DteResponse;
use App\Services\Dte\Post\Class\DteApiResponse;
use App\Services\Dte\Post\Class\DteApiSend;
use App\Services\Dte\Get\DteApiFactory;

class DteApiPostManager
{    
    // throw new \Exception('Fallé a propósito');

    public Company $company;

    public function __construct()
    {
        $this->company = Company::query()->firstOrFail();
    }

    public function send(Document $document)
    {    
        
            // $apiUrl = $this->company->url;

            // if (!DteApiConexion::hasConexion($apiUrl)) {
            //     return new DteResponse(
            //         accepted: false,
            //         message: 'No hay conexión a internet o el proveedor DTE no responde',
            //         errors: []
            //     );
            // } 

            $payload  = DteApiFactory::resolve($document)->mapJsonDte();   
            $apiResponse = DteApiSend::send($payload, $this->company,$document->id);  
            $responseService = DteApiResponse::manager($apiResponse, $document);         

         
    }

    
}


