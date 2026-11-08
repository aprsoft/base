<?php

namespace App\Services\Dte\Get;

use App\Services\Dte\Get\Classes\DteApiGet;


class DteGetManager
{ 
    public function get()
    {   

         return DteApiGet::get();    

    } 
}