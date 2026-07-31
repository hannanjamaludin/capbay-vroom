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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->enum('status', [
                'registered',
                'test_drive_scheduled',
                'test_drive_completed',
                'purchased',
                'cancelled'
            ])->default('registered');
            $table->timestamp('registered_at');
            $table->timestamp('test_drive_scheduled_at')->nullable();
            $table->timestamp('test_drive_completed_at')->nullable();
            $table->unsignedBigInteger('down_payment_sen');
            $table->boolean('paid_down_payment')->default(false);
            $table->unsignedBigInteger('vehicle_price_sen');
            $table->unsignedBigInteger('applied_discount_sen')->nullable();
            $table->unsignedBigInteger('final_price_sen')->nullable();
            $table->unsignedBigInteger('loan_amount_sen')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
            $table->index('registered_at');
            $table->index(['promotion_id', 'status']);
            $table->index(['vehicle_id', 'status']);
            $table->index(['promotion_id', 'paid_down_payment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
