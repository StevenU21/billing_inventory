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
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('sub_total')->default(0);
            $table->bigInteger('total');

            $table->enum('status', ['completed', 'cancelled', 'refunded'])->default('completed')->index();
            $table->boolean('is_credit')->default(false)->index();

            $table->dateTime('sale_date')->index();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('entities')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('sale_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('quantity', 16, 4);
            $table->bigInteger('unit_price');
            $table->bigInteger('unit_cost')->default(0);
            $table->bigInteger('sub_total');
            $table->decimal('conversion_factor_applied', 16, 4)->default(1);

            $table->boolean('discount')->default(false)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable()->default(0);
            $table->bigInteger('discount_amount')->nullable()->default(0);

            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->bigInteger('tax_amount')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('product_variant_id');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_details');
    }
};
