<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laad_sessies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('socket_id');
            $table->timestamp('start_time');
            $table->timestamp('stop_time')->nullable();
            $table->decimal('total_energy_begin', 10, 3);
            $table->decimal('total_energy_end', 10, 3)->nullable();
            $table->decimal('final_energy', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laad_sessies');
    }
}; 