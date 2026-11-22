<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('customers')->cascadeOnDelete();

            // Identitas QC
            $table->string('pic_qc', 100)->nullable();
            $table->date('tanggal_qc')->nullable();
            $table->foreignId('qc_by')->nullable()->constrained('users')->nullOnDelete();

            // Status call (dihitung otomatis dari qc_calls)
            // belum_dihubungi | in_progress | terhubung | gagal_dihubungi
            $table->string('final_call_status', 30)->default('belum_dihubungi');

            // Validasi data: valid | perlu_dikoreksi | belum_diverifikasi
            $table->string('validasi_nama', 30)->default('belum_diverifikasi');
            $table->string('validasi_email', 30)->default('belum_diverifikasi');
            $table->string('validasi_no_hp', 30)->default('belum_diverifikasi');
            $table->string('no_hp_alternatif', 255)->nullable();
            $table->string('validasi_alamat', 30)->default('belum_diverifikasi');
            // valid_customer | data_perlu_dikoreksi | belum_diverifikasi
            $table->string('hasil_validasi_data', 30)->default('belum_diverifikasi');

            // Validasi layanan: sesuai | tidak_sesuai | belum_diverifikasi
            $table->string('validasi_paket', 30)->default('belum_diverifikasi');
            $table->string('validasi_kecepatan', 30)->default('belum_diverifikasi');
            // valid_order | tidak_sesuai | belum_diverifikasi
            $table->string('hasil_validasi_experience', 30)->default('belum_diverifikasi');

            // Info tagihan: sudah_dijelaskan | belum_dijelaskan | tidak_relevan
            $table->string('info_biaya_bulanan', 30)->default('belum_dijelaskan');
            $table->string('info_siklus_prorata', 30)->default('belum_dijelaskan');
            $table->string('info_no_internet', 30)->default('belum_dijelaskan');
            // paham | perlu_diedukasi | belum_dikonfirmasi
            $table->string('pemahaman_billing', 30)->default('belum_dikonfirmasi');

            // Edukasi: sudah_dijelaskan | belum_dijelaskan
            $table->string('edukasi_118', 30)->default('belum_dijelaskan');
            $table->string('edukasi_my_telkomsel', 30)->default('belum_dijelaskan');
            $table->string('edukasi_jadwal_billing', 30)->default('belum_dijelaskan');
            $table->string('edukasi_larangan_reseller', 30)->default('belum_dijelaskan');

            // Penutup
            // edukasi_tersampaikan | perlu_edukasi | belum_dilakukan
            $table->string('hasil_close_call', 30)->default('belum_dilakukan');
            // in_progress | complete
            $table->string('qc_status', 20)->default('in_progress');
            $table->text('catatan')->nullable();

            // Audit: nama user yang membuat & terakhir mengubah
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            // Untuk filter & agregasi dashboard
            $table->index('final_call_status');
            $table->index('qc_status');
            $table->index('tanggal_qc');
            $table->index('pemahaman_billing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_records');
    }
};
