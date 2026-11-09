<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Dte\Get\DteGetManager;
use App\Services\Dte\Stamp\TimbrePdf417Service;
use Illuminate\Http\Request;


class DocumentController extends Controller
{
    public function index()
    {
        return view('documents.index');
    }

    public function create()
    {
        return view('documents.create');
    }  

    public function show(Document $document, bool $Api = False)
    {
         $dteGetManager = new DteGetManager;
         $response = $dteGetManager->manager('xml');

         $png = app(TimbrePdf417Service::class)->generar($response['xml']);
         return response($png, 200)
    ->header('Content-Type', 'image/png');


        // return view('documents.show',compact('document'));
    }
   



}
