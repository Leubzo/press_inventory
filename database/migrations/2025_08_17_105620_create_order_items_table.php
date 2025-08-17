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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('item_number');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_requested');
            $table->integer('quantity_fulfilled')->default(0);
            $table->decimal('unit_price', 8, 2);
            $table->timestamps();
            
            $table->index(['order_id', 'item_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
