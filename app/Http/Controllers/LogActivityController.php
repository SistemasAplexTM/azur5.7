<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	return view('logActivity');
    }

    public function getAll()
    {
        $logs = $this->logActivity();
        return \DataTables::of($logs)->make(true);
    }
}
