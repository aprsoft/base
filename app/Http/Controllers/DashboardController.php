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

            // $serviceDocument = new serviceFacturaAfecta();
            // $document = $serviceDocument->createDocument(15);

             $serviceDocument = new serviceNotaCredito();
             $document = $serviceDocument->createDocument(Document::find(106));
         

    //         $dteGetManager = new DteGetManager();
    //         $response = $dteGetManager->get();
    //    dd($response);
        
        $dteApiPostManager = new DtePostManager();
        $response = $dteApiPostManager->send($document);
        dd($response->toArray());
       
        

        return view('dashboard');
    }
}
