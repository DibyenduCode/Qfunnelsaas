<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class HomeController
{
    public function index(Request $request): Response
    {
        $plans = Database::query("SELECT * FROM plans WHERE is_active = 1 ORDER BY price ASC, id ASC");

        return view('home.index', [
            'title' => 'QFunnel — The Smarter Way to Build Interactive Quiz Funnels',
            'plans' => $plans,
            'currentUser' => current_user(),
        ], null);
    }
}

