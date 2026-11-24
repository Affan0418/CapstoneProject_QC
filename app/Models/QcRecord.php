<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcRecord extends Model
{
    protected $fillable = [
        'customer_id',
        'pic_qc',
        'tanggal_qc',
        'qc_by',
        'final_call_status',
        'validasi_nama',
        'validasi_email',
        'validasi_no_hp',
        'no_hp_alternatif',
        'validasi_alamat',
        'hasil_validasi_data',
        'validasi_paket',
        'validasi_kecepatan',
        'hasil_validasi_experience',
        'info_biaya_bulanan',
        'info_siklus_prorata',
        'info_no_internet',
        'pemahaman_billing',
        'edukasi_118',
        'edukasi_my_telkomsel',
        'edukasi_jadwal_billing',
        'edukasi_larangan_reseller',
        'hasil_close_call',
        'qc_status',
        'catatan',
        'created_by',
        'updated_by',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}