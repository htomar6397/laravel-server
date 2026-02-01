<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Reports Controller
 */
class AdminReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Reports/Index', [
            'filters' => $request->only(['date_from', 'date_to', 'type']),
        ]);
    }
}
