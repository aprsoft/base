<?php

namespace App\Services\Dte\Get\Classes;

use App\Models\Company;


class DteApiGet
{  
    
    public static function get(string $format)
    {   
      
        // dd($payload);  
        $company = Company::query()->firstOrFail();


        $curl = curl_init();

        $curlopt_url = 'https://api.haulmer.com/v2/dte/document';

        curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://dev-api.haulmer.com/v2/dte/document/76430498-5/61/118604/json',
        CURLOPT_URL => 'https://api.haulmer.com/v2/dte/document/74024900-2/61/712/'.$format,

        CURLOPT_RETURNTRANSFER => true,
        
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'apikey:1648a6daf01f462b9c3f63fcd7e4182a'
        ),
        ));

        $response = curl_exec($curl);



       return  json_decode($response, true);       

            
            

    }  
    
    

}