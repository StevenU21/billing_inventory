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
        Schema::create('entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('identity_card')->unique();
            $table->string('ruc')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_client')->default(false)->index();
            $table->boolean('is_supplier')->default(false)->index();
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('restrict')->onUpdate('cascade');
            $table->index(['is_client', 'is_supplier']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
