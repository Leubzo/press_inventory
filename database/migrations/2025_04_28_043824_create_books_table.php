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
    Schema::create('books', function (Blueprint $table) {
        $table->id(); // Laravel's auto primary key
        $table->string('isbn')->unique();
        $table->string('title');
        $table->string('authors_editors');
        $table->integer('year')->nullable();
        $table->integer('pages')->nullable();
        $table->decimal('price', 8, 2)->default(0.00);
        $table->string('category')->nullable();
        $table->string('other_category')->nullable();
        $table->integer('stock')->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
