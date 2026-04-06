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
        Schema::create('quotations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('sub_total')->default(0);
            $table->bigInteger('total');

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending')->index();

            $table->dateTime('date_issued')->default(now())->index();
            $table->dateTime('valid_until')->nullable()->index();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('entities')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('quotation_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('quantity', 16, 4);
            $table->string('currency', 3)->default('NIO');

            $table->boolean('discount')->default(false)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable()->default(0);
            $table->bigInteger('discount_amount')->nullable()->default(0);

            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->bigInteger('tax_amount')->default(0);

            $table->bigInteger('unit_price');
            $table->bigInteger('sub_total');

            $table->unsignedBigInteger('quotation_id');
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('quotation_details');
    }
};
