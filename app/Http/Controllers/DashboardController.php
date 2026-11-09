<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Document\serviceBoletaAfecta;
use App\Services\Document\serviceBoletaExenta;
use App\Services\Document\serviceFacturaAfecta;
use App\Services\Document\serviceNotaCredito;
use App\Services\Dte\Get\DteGetManager;
use App\Services\Dte\Post\DtePostManager;

class DashboardController extends Controller
{
    public function __invoke()
    {  
 

        // $serviceDocument = new serviceBoletaExenta();
        // $document = $serviceDocument->createDocument(12);

        // $serviceDocument = new serviceBoletaAfecta();
        // $document = $serviceDocument->createDocument(17);

            $serviceFacturaAfecta = new serviceFacturaAfecta();
            $document = $serviceFacturaAfecta->createDocument(15);

            $dtePostManager = new DtePostManager();
            $response = $dtePostManager->send($document);

            //dd($response);

            // if ($response['WARNING'])


            $serviceDocument = new serviceNotaCredito();
            $document2 = $serviceDocument->createDocument($document);

            $dteApiPostManager = new DtePostManager();
            $response = $dteApiPostManager->send($document);
         

    //         $dteGetManager = new DteGetManager();
    //         $response = $dteGetManager->get();
        dd($response);
        
        $dteApiPostManager = new DtePostManager();
        $response = $dteApiPostManager->send($document);
        dd($response->toArray());
       
        

        return view('dashboard');
    }
}
