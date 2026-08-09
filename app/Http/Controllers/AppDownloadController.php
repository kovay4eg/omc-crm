<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AppDownloadController extends Controller
{
    public function __invoke(): View
    {
        return view('app-download', [
            'androidDownloadUrl' => route('app.download.android'),
            'androidVersion' => config('mobile.updates.android.latest_version'),
        ]);
    }
}
