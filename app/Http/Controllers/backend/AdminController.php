<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        $this->authorize('access-admin-panel');
        return view('backend.dashboard');
    }
}
