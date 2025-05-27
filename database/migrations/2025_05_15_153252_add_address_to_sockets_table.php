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
        Schema::table('sockets', function (Blueprint $table) {
            // Verwijder de location kolom als deze bestaat
            if (Schema::hasColumn('sockets', 'location')) {
                $table->dropColumn('location');
            }
            
            // Voeg de address kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('sockets', 'address')) {
                $table->string('address')->default('Onbekend')->after('socket_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sockets', function (Blueprint $table) {
            // Verwijder de address kolom als deze bestaat
            if (Schema::hasColumn('sockets', 'address')) {
                $table->dropColumn('address');
            }
            
            // Voeg de location kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('sockets', 'location')) {
                $table->string('location')->default('Onbekend')->after('socket_id');
            }
        });
    }
}; 