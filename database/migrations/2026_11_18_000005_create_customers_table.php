<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Identitas layanan
            $table->unsignedBigInteger('service_id')->unique();
            $table->string('order_id', 50)->unique(); // alfanumerik, contoh: AOi426110501012794134ae10

            // Wilayah & agency
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->nullOnDelete();
            $table->string('cluster', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('sto', 50)->nullable();

            // Sales force
            $table->string('team_leader', 100)->nullable();
            $table->string('kode_sf', 50)->nullable();
            $table->string('nama_sf', 100)->nullable();
            $table->foreign('kode_sf')->references('kode_sf')->on('sales')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('nama_sf')->references('nama_sf')->on('sales')
                ->cascadeOnUpdate()->restrictOnDelete();

            // Pelanggan
            $table->string('nama_pelanggan', 150);
            $table->string('email', 150)->nullable();
            $table->string('no_hp_utama', 20)->nullable(); // string agar angka 0 di depan aman
            $table->text('alamat_pemasangan')->nullable();

            // Produk & tagihan
            $table->string('paket', 150)->nullable();
            $table->decimal('harga_paket', 12, 0)->default(0);
            $table->decimal('biaya_pasang', 12, 0)->default(0);
            $table->date('tanggal_ps')->nullable();
            $table->date('tanggal_mulai_bayar_tagihan')->nullable();

            // Audit: nama user yang membuat & terakhir mengubah
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->index('tanggal_ps');
            $table->index(['branch_id', 'agency_id']);
            $table->index('no_hp_utama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
