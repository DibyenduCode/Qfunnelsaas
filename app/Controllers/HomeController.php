<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class HomeController
{
    public function index(Request $request): Response
    {
        $plans = [];
        try {
            $plans = Database::query("SELECT * FROM plans WHERE is_active = 1 ORDER BY price ASC, id ASC");
        } catch (\Throwable $e) {
            $plans = [];
        }

        return view('home.index', [
            'title' => 'QFunnel — Interactive Quiz Funnel & Lead Qualification SaaS',
            'plans' => $plans,
            'currentUser' => current_user(),
        ], null);
    }
}

