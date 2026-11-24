<?php

namespace App\Http\Controllers;

class RegionDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.region');
    }
}