<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('stock', 16, 4)->index();
            $table->bigInteger('average_cost')->default(0);
            $table->decimal('min_stock', 16, 4)->index();
            $table->timestamp('low_stock_notified_at')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('product_variant_id');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', [
                'purchase',
                'sale_return',
                'sale_cancellation',
                'adjustment_in',
                'sale',
                'purchase_return',
                'adjustment_out'
            ])->nullable();
            $table->enum('adjustment_reason', [
                'correction',
                'physical_count',
                'damage',
                'theft',
                'expiration',
                'production',
                'return'
            ])->nullable();

            $table->decimal('quantity', 16, 4);

            $table->decimal('stock_before', 16, 4)->nullable();
            $table->decimal('stock_after', 16, 4)->nullable();

            $table->bigInteger('unit_price');
            $table->bigInteger('total_price');

            $table->nullableMorphs('sourceable');

            $table->string('notes')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventory_movements');
    }
};
