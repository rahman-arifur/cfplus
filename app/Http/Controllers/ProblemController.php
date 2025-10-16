<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProblemController extends Controller
{
    /**
     * Display the problems page.
     */
    public function index(Request $request): View
    {
        return view('problems.index');
    }
}
