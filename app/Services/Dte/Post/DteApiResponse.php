<?php

namespace App\Services\Dte\Post;

use App\Models\ErrorDte;
use Throwable;


class DteApiResponse
{ 
    public static function manager($response, $document)
    {
        try{
                if (isset($response['WARNING'])) {

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

        }catch(Throwable $e){

           throw $e; 

        }
        
    } 
}