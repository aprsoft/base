<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $routeName = $request->route()->getName();  

        return view('meters.index',compact('routeName'));
    } 


   
    public function edit(string $id)
    {
        //
    }


}
