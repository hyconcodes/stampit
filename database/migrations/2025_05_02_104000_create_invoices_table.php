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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rrr')->unique();
            $table->decimal('amount', 10, 2);
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('invoice')->unique();
            $table->string('caption')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // pending, approved, rejected
            $table->string('comment')->nullable(); // comment from the admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
