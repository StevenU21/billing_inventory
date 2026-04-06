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
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status', ['draft', 'ordered', 'received', 'cancelled'])->default('draft')->index();
            $table->boolean('is_credit')->default(false);
            $table->string('reference')->nullable()->index();

            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('sub_total')->nullable()->default(0);
            $table->bigInteger('total')->nullable()->default(0);

            $table->dateTime('purchase_date')->index();
            $table->timestamp('received_at')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('entities')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('purchase_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('quantity', 16, 4);
            $table->bigInteger('unit_price');

            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('product_variant_id');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('purchase_details');
    }
};
