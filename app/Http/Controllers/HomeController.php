<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company; // Import the Company model

class HomeController extends Controller

{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::first(); // Fetch the first company record
        return view('home', compact('company')); // Pass the company data to the view
    }



}
