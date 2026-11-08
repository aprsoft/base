<?php

namespace App\Services\Dte\Get\Classes;

use App\Models\Company;


class DteApiGet
{  
    
    public static function get()
    {   
      
        // dd($payload);  
        $company = Company::query()->firstOrFail();


        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://dev-api.haulmer.com/v2/dte/document/76430498-5/33/26005/json',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'apikey: 928e15a2d14d4a6292345f04960f4bd3'
        ),
        ));

        $response = curl_exec($curl);



        curl_close($curl);
       return  json_decode($response, true);       

            
            

    }  
    
    

}