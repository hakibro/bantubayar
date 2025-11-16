<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiswaService;
use Illuminate\Http\Request;

class AssignController extends Controller
{
    function index()
    {
        return view('admin.assign.index');
    }

}
