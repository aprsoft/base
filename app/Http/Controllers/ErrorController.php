<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;

class ErrorController extends Controller
{
    public function index()
    {
        return view('errors.index');
    }

    public function show(ErrorLog $errorLog)
    {
        return view('errors.show',compact('errorLog'));
    }
}