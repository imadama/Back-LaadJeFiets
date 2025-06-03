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
            $table->dropColumn(['name', 'ip', 'port']);
            $table->string('socket_id')->after('user_id');
            $table->unsignedBigInteger('location_id')->after('socket_id'); // Add location_id column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sockets', function (Blueprint $table) {
            $table->string('name');
            $table->string('ip');
            $table->integer('port');
            $table->dropColumn(['socket_id', 'location_id']); // Drop both columns
        });
    }
};