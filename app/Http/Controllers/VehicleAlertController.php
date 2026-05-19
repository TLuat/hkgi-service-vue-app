<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\VehicleAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VehicleAlertController extends Controller
{
    private const OWNER_ONLY = ['owner'];

    // ── import ─────────────────────────────────────────────────────────────

    public function import(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // First row is header — skip it
        $header = array_shift($rows);

        $upserted = 0;

        foreach ($rows as $row) {
            $licensePlate = isset($row[0]) ? strtoupper(str_replace(' ', '', (string) $row[0])) : null;

            if (! $licensePlate) {
                continue;
            }

            VehicleAlert::updateOrCreate(
                ['license_plate' => $licensePlate],
                [
                    'customer_name' => (string) ($row[1] ?? ''),
                    'phone_number'  => (string) ($row[2] ?? ''),
                    'invoice_no'    => isset($row[3]) && $row[3] !== '' ? (string) $row[3] : null,
                    'contract_no'   => isset($row[4]) && $row[4] !== '' ? (string) $row[4] : null,
                    'points'        => isset($row[5]) ? (int) $row[5] : 0,
                ],
            );

            $upserted++;
        }

        ActivityLogger::log(
            'Nhập cảnh báo xe',
            "Đã nhập/cập nhật {$upserted} bản ghi cảnh báo xe",
            $request->user()->name,
        );

        return back()->with('success', "Đã nhập {$upserted} bản ghi cảnh báo xe");
    }
}