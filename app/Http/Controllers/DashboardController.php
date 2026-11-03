<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Dte\Post\DtePostManager;
use App\Services\Document\servicedocument;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {  
 

        $serviceDocument = new serviceDocument();
        $document = $serviceDocument->createDocument(17);
        
        $DtePostManager = new DtePostManager();
        $DtePostManager->post($document);
       


        return view('dashboard');
    }
}
