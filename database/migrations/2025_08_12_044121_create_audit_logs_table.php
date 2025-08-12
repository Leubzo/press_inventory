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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('user_source')->default('appsheet'); // 'appsheet', 'web', 'api'
            $table->string('user_identifier')->nullable(); // AppSheet user email
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
