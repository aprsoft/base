<?php

namespace App\Services\Dte\Post\Classes;

use App\Models\Document;
use App\Models\ErrorDte;
use App\Services\Dte\Post\Classes\DteResponse;


class DteApiResponse
{ 
    public static function manager(array $response, Document $document)
    {
      

        if (isset($response['TIMBRE']) && !isset($response['WARNING'])) {

            return new DteResponse(
                accepted: true,
                folio: $response['FOLIO'],         
                
            );          

        }
        if (isset($response['TIMBRE']) && isset($response['WARNING'])) {

            ErrorDte::create([
                'document_id' => $document->id,
                'warning'     => json_encode($response['WARNING']),                    
            ]);  

            
            return new DteResponse(
                accepted: true,
                folio: $response['FOLIO'],                    
                warnings: $response['WARNING'],
                
            );           

        }else {

            ErrorDte::create([
                    'document_id' => $document->id,
                    'error'     => json_encode($response),                    
            ]);

            return new DteResponse(
                accepted: false,
                message:$response['error']['message'],
                
            );   
        }       
    } 
}