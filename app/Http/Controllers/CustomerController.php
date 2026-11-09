<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $routeName = $request->route()->getName();        

        return view('customers.index',compact('routeName'));
    }

 
    public function create()
    {
        return view('tenant.admin.customers.create');
    }


   
    public function show(Customer $customer)
    {
        return view('tenant.admin.customers.show',compact('customer'));
    }

   
    public function edit(string $id)
    {
        //
    }


}
