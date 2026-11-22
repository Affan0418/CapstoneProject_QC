<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // kode_sf & nama_sf harus UNIQUE karena dirujuk sebagai foreign key oleh customers
            $table->string('kode_sf', 50)->unique();
            $table->string('nama_sf', 100)->unique();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();

            // Audit: nama user yang membuat & terakhir mengubah
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
