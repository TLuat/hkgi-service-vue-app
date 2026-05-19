<?php

namespace App\Http\Controllers;

use App\Models\PendingIntake;
use Inertia\Inertia;
use Inertia\Response;

class IntakeDisplayController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('IntakeDisplay', [
            'intakes' => PendingIntake::orderBy('arrived_at')->get(),
        ]);
    }
}