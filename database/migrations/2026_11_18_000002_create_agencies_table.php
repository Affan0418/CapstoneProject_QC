<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('pic_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            // Audit: nama user yang membuat & terakhir mengubah
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
