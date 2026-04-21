<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;

class HomeController extends Controller
{
    public function index()
    {
        $settings = HomepageSetting::first();

        return view('index', compact('settings'));
    }
}