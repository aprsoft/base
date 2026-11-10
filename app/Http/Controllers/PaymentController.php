<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Meter;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function indexCreate(Request $request)
    {
        $routeName = $request->route()->getName();      

        return view('payments.index-create',compact('routeName'));
    }

    public function create(Request $request, ?Customer $customer = null, ?Meter $meter = null)
    {
        $routeName = $request->route()->getName(); 

        // return view('payments.create',array_filter(['routeName'=>$routeName,'meter'=>$meter,'customer'=>$customer]));
        return view('payments.create',array_filter(['routeName'=>$routeName,'meter'=>$meter,'customer'=>$customer]));

    }
   
    public function show(Payment $payment)
    {
        return view('payments.show',compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        return view('tenant.admin.payments.destroy');
    }
}
