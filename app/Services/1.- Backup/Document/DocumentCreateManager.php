<?php

namespace App\Services\Document;

use App\Models\Document;

class DocumentCreateManager
{
    public function create($request) : Document
    {       
        $document = DocumentFactory::resolve($request);   
        return $document->create($request);

    }

}