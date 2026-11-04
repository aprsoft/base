<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Document\serviceBoletaAfecta;
use App\Services\Document\serviceBoletaExenta;
use App\Services\Dte\Post\DteApiPostManager;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {  
 

        $serviceDocument = new serviceBoletaExenta();
        $document = $serviceDocument->createDocument(12);

        // $serviceDocument = new serviceBoletaAfecta();
        // $document = $serviceDocument->createDocument(17);
        
        $dteApiPostManager = new DteApiPostManager();
        $dteApiPostManager->send($document);
       
        dd(8);

        return view('dashboard');
    }
}
