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


    public function show(int $id)
    {
        Gate::authorize('errors.show');

        $record = ErrorLog::findOrFail($id);

        Gate::authorize('view', $record);

        return view('errors.show', ['errorLog' => $record]);
    }
}