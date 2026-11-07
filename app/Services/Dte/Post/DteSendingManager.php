<?php

namespace App\Services\Dte\Post;

use App\Models\Document;
use App\Services\Dte\Post\Classes\DteApiFactory;
use App\Services\Dte\Post\Classes\DteApiResponse;
use App\Services\Dte\Post\Classes\DteApiSend;


class DteSendingManager
{   
    public function send(Document $document)
    {   
       $jsonSendDte     = DteApiFactory::resolve($document)->mapJsonDte();    
       $jsonResponseDte = DteApiSend::send($jsonSendDte, $document->id);

       dd($jsonResponseDte);

       return DteApiResponse::manager($jsonResponseDte, $document);          

    } 
}