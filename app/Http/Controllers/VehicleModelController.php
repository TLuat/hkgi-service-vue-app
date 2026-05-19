<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    private const OWNER_ONLY = ['owner'];

    // ── store ──────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_models,name',
        ]);

        $model = VehicleModel::create($data);

        ActivityLogger::log(
            'Thêm dòng xe',
            "Thêm dòng xe '{$model->name}'",
            $request->user()->name,
            'vehicle_model',
            $model->id,
        );

        return back()->with('success', "Đã thêm dòng xe {$model->name}");
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function destroy(Request $request, VehicleModel $model): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $name    = $model->name;
        $modelId = $model->id;

        $model->delete();

        ActivityLogger::log(
            'Xóa dòng xe',
            "Đã xóa dòng xe '{$name}'",
            $request->user()->name,
            'vehicle_model',
            $modelId,
        );

        return back()->with('success', "Đã xóa dòng xe {$name}");
    }
}