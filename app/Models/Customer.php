<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'service_id',
        'order_id',
        'branch_id',
        'agency_id',
        'cluster',
        'city',
        'sto',
        'team_leader',
        'kode_sf',
        'nama_sf',
        'nama_pelanggan',
        'email',
        'no_hp_utama',
        'alamat_pemasangan',
        'paket',
        'harga_paket',
        'biaya_pasang',
        'tanggal_ps',
        'tanggal_mulai_bayar_tagihan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'harga_paket' => 'decimal:0',
        'biaya_pasang' => 'decimal:0',
        'tanggal_ps' => 'date',
        'tanggal_mulai_bayar_tagihan' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'kode_sf', 'kode_sf');
    }

    public function qcRecord(): HasOne
    {
        return $this->hasOne(QcRecord::class);
    }
}
