<?php

namespace App\Services\Dte\Get;

use App\Models\Company;

class DteApiSupplierGet
{  
    
    public static function get(int $page, $date)
    {        
        
        $company   = Company::query()->firstOrFail();
        $apikey    = $company->apikey;
        $url       = $company->url_document_received;
        $rutEmisor = $company->rut_emisor;

        $apikey    = '1648a6daf01f462b9c3f63fcd7e4182a';
       

        $curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL =>$url,
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
		CURLOPT_POSTFIELDS =>'{"Page":"'.$page.'","FchEmis":{"gte":"'.$date.'"}}',
		CURLOPT_HTTPHEADER => array(
		'apikey:'.$apikey,
		"Content-Type: application/json"
		),
		));


		$response = curl_exec($curl);

		return json_decode($response);           

    }  
    
    

}