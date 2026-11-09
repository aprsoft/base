<?php

namespace App\Services\Dte\Get;

use App\Services\Dte\Get\Classes\DteApiGet;


class DteGetManager
{ 
    public function manager(string $format)
    {    

        //  return DteApiGet::get(string $rutEmisor, int $tipoDte, int $folio);    

        return DteApiGet::get($format);    

    } 
}