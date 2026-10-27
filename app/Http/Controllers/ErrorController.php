<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Gate;

class ErrorController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', ErrorLog::class);
        return view('errors.index');
    }

    public function show(ErrorLog $errorLog)
    {dd(8);
        Gate::authorize('view', $errorLog);
        return view('errors.show',compact('errorLog'));
    }
}