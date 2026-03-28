<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SportsField; // Import model SportsField

class PageController extends Controller
{
    /**
     * Display the welcome page with featured sports fields.
     */
    public function welcome()
    {
        $courts = SportsField::where('status', 'active')
            ->latest() 
            ->take(3)  
            ->get();

        return view('welcome', compact('courts'));
    }
}
