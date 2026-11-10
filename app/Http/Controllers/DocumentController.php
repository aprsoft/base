<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Meter;
use App\Models\Supplier;
use App\Services\Dte\Get\DteGetManager;
use App\Services\Dte\Stamp\TimbrePdf417Service;
use Illuminate\Http\Request;


class DocumentController extends Controller
{
    public function index(Request $request, ?Supplier $supplier = null, ?Customer $customer = null, ?Meter $meter = null) 
    {
        $route      = $request->route();
        $hasParams  = ! empty($route->parameters());


        $entity = $hasParams
            ? null
            : match ($route->getName()) {
                'documents.customer.index'   => 'customers',
                'documents.supplier.index'   => 'suppliers',
                'documents.customer.overdue' => 'overdue',
                default                      => 'all',
            };

        return view('documents.index', [
            'entity'   => $entity,
            'customer' => $customer,
            'supplier' => $supplier,
            'meter'    => $meter,
            'title'    => 'test'
        ]); 
    }

    public function show(Document $document)
    {
          
        return view('documents.show',compact('document'));
    } 



    public function create()
    {
        return view('documents.create');
    }  

    // public function show(Document $document, bool $Api = False)
    // {
    //     $dteGetManager = new DteGetManager;
    //     $response = $dteGetManager->manager('xml');

    //     $stamp = app(TimbrePdf417Service::class)->generar($response['xml']);
      


    //     return view('documents.show',compact('document','stamp'));
    // }
   



}
