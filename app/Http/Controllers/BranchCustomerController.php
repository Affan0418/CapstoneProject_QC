<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Agency;

class BranchCustomerController extends Controller
{
    public function store(StoreCustomerRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $customer = DB::transaction(function () use ($validated, $user) {
            // Ambil nama_sf otomatis dari tabel sales jika kode_sf diisi
            $namaSf = null;
            if (!empty($validated['kode_sf'])) {
                $sale = Sale::where('kode_sf', $validated['kode_sf'])
                    ->where('branch_id', $user->branch_id)
                    ->first();
                $namaSf = $sale?->nama_sf;
            }

            // 1. Simpan data pelanggan
            $customer = Customer::create([
                ...$validated,
                'branch_id' => $user->branch_id,
                'nama_sf' => $namaSf,
                'harga_paket' => $validated['harga_paket'] ?? 0,
                'biaya_pasang' => $validated['biaya_pasang'] ?? 0,
                'created_by' => $user->name,
                'updated_by' => $user->name,
            ]);

            // 2. Buat record QC default agar langsung masuk antrean "Belum QC"
            $customer->qcRecord()->create([
                'final_call_status' => 'belum_dihubungi',
                'qc_status' => 'in_progress',
                'created_by' => $user->name,
                'updated_by' => $user->name,
            ]);

            return $customer->load(['branch', 'agency', 'qcRecord']);
        });

        // Jika dites via Postman / AJAX (Accept: application/json)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data pelanggan berhasil ditambahkan.',
                'data' => $customer,
            ], 201);
        }

        // Jika disubmit lewat form HTML biasa nanti
        return redirect()
            ->route('branch.dashboard')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function create()
    {
        $agencies = Agency::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('customers.create', compact('agencies'));
    }
}
