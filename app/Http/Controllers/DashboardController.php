<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\PendingIntake;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\VehicleAlert;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $tickets = ServiceTicket::orderBy('created_at', 'desc')->get();

        $summary = [
            'openCount'       => ServiceTicket::open()->count(),
            'waitingDispatch' => ServiceTicket::where('kanban_stage', 'Chờ điều phối')->count(),
            'inProgress'      => ServiceTicket::where('kanban_stage', 'Đang thực hiện')->count(),
            'waitingParts'    => ServiceTicket::where('waiting_parts', true)->open()->count(),
            'handoffReady'    => ServiceTicket::where('kanban_stage', 'Chờ giao xe')->count(),
            'urgentCases'     => ServiceTicket::urgent()->count(),
        ];

        $pendingIntakes = PendingIntake::orderBy('arrived_at', 'desc')->get();

        $vehicleAlerts = VehicleAlert::all()->keyBy('license_plate');

        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->limit(200)->get();

        $vehicleModels = VehicleModel::orderBy('name')->pluck('name');

        $accountUsers = $request->user()->role === 'owner'
            ? User::all()
            : collect();

        $settings = AppSetting::first();

        return Inertia::render('Dashboard', compact(
            'tickets',
            'summary',
            'pendingIntakes',
            'vehicleAlerts',
            'activityLogs',
            'vehicleModels',
            'accountUsers',
            'settings',
        ));
    }

    public function kanban(): Response
    {
        $tickets = ServiceTicket::orderBy('check_in_at', 'desc')->get();

        $vehicleAlerts = VehicleAlert::all()->keyBy('license_plate');

        return Inertia::render('Kanban', compact('tickets', 'vehicleAlerts'));
    }

    public function departments(): Response
    {
        $tickets = ServiceTicket::open()
            ->orderBy('check_in_at', 'desc')
            ->get();

        return Inertia::render('Departments', compact('tickets'));
    }

    public function customers(): Response
    {
        $tickets = ServiceTicket::orderBy('check_in_at', 'desc')->get();

        return Inertia::render('Customers', compact('tickets'));
    }
}