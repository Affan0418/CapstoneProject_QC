<?php

namespace App\Http\Controllers;

class BranchDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.branch');
    }
}