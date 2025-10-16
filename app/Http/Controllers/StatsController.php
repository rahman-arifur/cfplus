<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StatsController extends Controller
{
    /**
     * Display the stats page.
     */
    public function index(Request $request): View
    {
        return view('stats.index');
    }
}
