<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Document\serviceBoletaAfecta;
use App\Services\Document\serviceBoletaExenta;

use App\Services\Dte\Post\DteSendingManager;

class DashboardController extends Controller
{
    public function __invoke()
    {  
 

        $serviceDocument = new serviceBoletaExenta();
        $document = $serviceDocument->createDocument(12);

        // $serviceDocument = new serviceBoletaAfecta();
        // $document = $serviceDocument->createDocument(17);
        
        $dteApiPostManager = new DteSendingManager();
        $response = $dteApiPostManager->send($document);
        dd($response->toArray());
       
        

        return view('dashboard');
    }
}
