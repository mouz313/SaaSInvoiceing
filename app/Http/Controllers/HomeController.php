<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('welcome', compact('packages'));
    }
}
