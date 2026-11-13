<?php

namespace App\Services\Dte\Get;

use App\Models\Company;
use App\Models\Document;


class DteApiCompanieGet
{  
    
    public static function get(Document $document, string $format)
    {         
        $company   = Company::query()->firstOrFail();
        $apikey    = $company->apikey;
        $url       = $company->url_document;
        $rutEmisor = $company->rut_emisor;
        $dteCode   = $document->typedocument->codigo;
        $folio     = $document->folio;

        $apikey    = '1648a6daf01f462b9c3f63fcd7e4182a'; // produccion


        $curl = curl_init();

        $curlopt_url = 'https://api.haulmer.com/v2/dte/document';

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url.'/74024900-2/'.$dteCode.'/'.$folio.'/'.$format,
        // CURLOPT_URL => $url.'/'.$rutEmisor.'/'.$dteCode.'/'.$folio.'/'.$format,

        CURLOPT_RETURNTRANSFER => true,
        
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'apikey:'.$apikey
        ),
        ));

        $response = curl_exec($curl);



       return  json_decode($response, true);       

            
            

    }  
    
    

}