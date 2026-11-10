<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMeter;
use Illuminate\Http\Request;

class ContractController extends Controller
{
 
    public function index(Request $request)
    {        

        $routeName = $request->route()->getName();

        $estadoContrato = match ($routeName) {
            'contracts.active.index'     => 'VIGENTE',
            'contracts.terminated.index' => 'TERMINADO',
            default => 'all',
        };

        return view('contracts.index',compact('estadoContrato') );
    }
   
    public function create()
    {
        return view('contracts.create');
    }

    public function show(CustomerMeter $contract)
    {
        return view('contracts.show',compact('contract') );
    }

    public function edit(CustomerMeter $contract)
    {
        return view('contracts.edit',compact('contract') );
    }
   

}
