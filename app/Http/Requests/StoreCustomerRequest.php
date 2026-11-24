<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role === User::ROLE_BRANCH
            && $user->branch_id !== null
            && $user->is_active;
    }

    public function rules(): array
    {
        $branchId = $this->user()->branch_id;

        return [
            'service_id' => ['required', 'integer', 'min:1', 'unique:customers,service_id'],
            'order_id' => ['required', 'string', 'max:50', 'unique:customers,order_id'],
            'agency_id' => [
                'nullable',
                'integer',
                Rule::exists('agencies', 'id')->where('is_active', true),
            ],
            'cluster' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'sto' => ['nullable', 'string', 'max:50'],
            'team_leader' => ['nullable', 'string', 'max:100'],
            'kode_sf' => [
                'nullable',
                'string',
                'max:50',
                // Pastikan kode_sf ada di tabel sales DAN milik branch admin yang login
                Rule::exists('sales', 'kode_sf')->where('branch_id', $branchId),
            ],
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'no_hp_utama' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+]+$/'],
            'alamat_pemasangan' => ['nullable', 'string'],
            'paket' => ['nullable', 'string', 'max:150'],
            'harga_paket' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'biaya_pasang' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'tanggal_ps' => ['nullable', 'date'],
            'tanggal_mulai_bayar_tagihan' => ['nullable', 'date', 'after_or_equal:tanggal_ps'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.unique' => 'Service ID sudah terdaftar di sistem.',
            'order_id.unique' => 'Order ID sudah terdaftar di sistem.',
            'kode_sf.exists' => 'Kode SF tidak ditemukan atau bukan bagian dari Branch Anda.',
            'no_hp_utama.regex' => 'Nomor HP hanya boleh berisi angka.',
        ];
    }
}