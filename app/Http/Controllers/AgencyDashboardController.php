<?php

namespace App\Http\Controllers;

class AgencyDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.agency');
    }
}