<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Schema::table('laad_sessies', function (Blueprint $table) {
        //     $table->decimal('total_energy_begin', 10, 3)->change();
        //     $table->decimal('total_energy_end', 10, 3)->nullable()->change();
        //     $table->decimal('final_energy', 10, 3)->nullable()->change();
        // });
    }
    
    public function down()
    {
        // Schema::table('laad_sessies', function (Blueprint $table) {
        //     $table->decimal('total_energy_begin', 10, 2)->change();
        //     $table->decimal('total_energy_end', 10, 2)->nullable()->change();
        //     $table->decimal('final_energy', 10, 2)->nullable()->change();
        // });
    }
    
};
