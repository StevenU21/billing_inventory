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
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('opening_balance')->default(0);
            $table->bigInteger('expected_closing_balance')->default(0);
            $table->bigInteger('actual_closing_balance')->nullable();
            $table->bigInteger('difference')->nullable();

            $table->enum('status', [
                'open',
                'closed',
                'suspended',
            ])->default('open')->index();
            $table->string('currency', 3)->default('NIO');

            $table->dateTime('opened_at')->index();
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('opened_by');
            $table->foreign('opened_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('closed_by')->nullable();
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->index(['user_id', 'opened_at']);
            $table->timestamps();
        });

        Schema::create('cash_register_movements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->enum('type', [
                'sale',
                'deposit',
                'adjustment_in',
                'refund',
                'withdrawal',
                'adjustment_out',
                'purchase',
                'receivable_payment'
            ])->index();

            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->string('currency', 3)->default('NIO');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('description')->nullable();
            $table->dateTime('movement_at')->index();

            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('cash_register_sessions')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict')->onUpdate('cascade');

            $table->index(['reference_type', 'reference_id']);
            $table->index(['session_id', 'movement_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_movements');
        Schema::dropIfExists('cash_register_sessions');
    }
};
