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
        Schema::create('account_payables', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('total_amount');
            $table->bigInteger('balance');
            $table->bigInteger('amount_paid')->default(0);
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'voided'])->default('pending')->index();

            $table->date('due_date')->nullable()->index();
            $table->string('currency', 3)->default('NIO');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('entities')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('account_payables_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('amount');
            $table->string('reference')->nullable()->index();
            $table->string('notes')->nullable();
            $table->string('currency', 3)->default('NIO');

            $table->dateTime('payment_date')->nullable()->index();

            $table->unsignedBigInteger('account_payable_id');
            $table->foreign('account_payable_id')->references('id')->on('account_payables')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('entities')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payables');
        Schema::dropIfExists('account_payables_payments');
    }
};
