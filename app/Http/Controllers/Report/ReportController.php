<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {   
        $elections = Election::latest()->get();
        return Inertia::render('Reports/Results/Index', [
            'elections' => $elections
        ]);
    }

    public function show(Election $election)
{
    return Inertia::render('Reports/Results/Show', [
        'election' => $election,
        // Include any additional data needed for the results page
    ]);
}
}