<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_record_id')->constrained('qc_records')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_no'); // 1 s/d 3
            $table->dateTime('called_at')->nullable();
            // connected | no_answer | busy | rejected | invalid_number | not_connected
            $table->string('status', 20);
            // Audit: nama user yang membuat & terakhir mengubah
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['qc_record_id', 'attempt_no']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_calls');
    }
};
