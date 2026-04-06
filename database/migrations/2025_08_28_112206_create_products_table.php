<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('value');
            $table->string('abbreviation')->nullable();

            $table->foreignId('product_attribute_id')->constrained()->restrictOnDelete()->index();
            $table->unique(['product_attribute_id', 'value']);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index();
            $table->string('code')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->enum('status', ['draft', 'available', 'archived'])->default('draft')->index();

            $table->foreignId('brand_id')->constrained()->restrictOnDelete()->index();
            $table->foreignId('tax_id')->constrained()->restrictOnDelete()->index();
            $table->foreignId('unit_measure_id')->constrained()->restrictOnDelete()->index();

            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('search_text')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();

            $table->bigInteger('price');
            $table->bigInteger('cost')->nullable();
            $table->bigInteger('credit_price')->nullable();
            $table->decimal('conversion_factor', 16, 4)->default(1);

            $table->string('image')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->foreignId('product_id')->constrained()->restrictOnDelete()->index();
            $table->timestamps();
        });

        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete()->index();
            $table->foreignId('product_attribute_value_id')->constrained()->restrictOnDelete()->index();
            $table->unique(['product_variant_id', 'product_attribute_value_id'], 'variant_value_unique');
        });

        Schema::create('product_price_history', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('old_price');
            $table->unsignedBigInteger('new_price');

            $table->unsignedBigInteger('old_cost')->nullable();
            $table->unsignedBigInteger('new_cost')->nullable();

            $table->string('notes')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete()->index();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_history');
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_attributes');
    }
};
