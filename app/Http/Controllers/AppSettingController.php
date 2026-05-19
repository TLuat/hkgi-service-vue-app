<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppSettingController extends Controller
{
    private const OWNER_ONLY = ['owner'];

    // ── index ──────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        return Inertia::render('Control', [
            'settings'      => AppSetting::first(),
            'vehicleModels' => VehicleModel::all(),
            'activityLogs'  => ActivityLog::latest()->limit(200)->get(),
        ]);
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function update(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $data = $request->validate([
            'storage_mode'   => 'sometimes|in:local,google_sheet',
            'google_sheet_id'=> 'sometimes|nullable|string|max:255',
        ]);

        $settings = AppSetting::firstOrCreate([]);
        $settings->update($data);

        ActivityLogger::log(
            'Cập nhật cài đặt',
            'Cập nhật cài đặt hệ thống',
            $request->user()->name,
        );

        return back()->with('success', 'Đã lưu cài đặt');
    }
}