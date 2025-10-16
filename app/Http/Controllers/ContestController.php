<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ContestController extends Controller
{
    /**
     * Display the contests page.
     */
    public function index(Request $request): View
    {
        return view('contests.index');
    }
}
