<?php

namespace App\Services\Dte\Post\Class;


class DteApiSend
{  
    
    public static function send($payload, $company, $IdempotencyKey)
    {   
         $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $company->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    "apikey:".$company->apikey,
                    // "Idempotency-Key:".$IdempotencyKey,
                    "Content-Type: application/json",
                ],
            ]);

            $response = curl_exec($curl); 

            return json_decode($response, true);   
    }   

}