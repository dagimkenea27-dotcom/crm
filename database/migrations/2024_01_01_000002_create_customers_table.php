<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->date('joined_on')->nullable();
            $table->string('location')->nullable();
            $table->string('account_status')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->string('source_order_id')->nullable();
            $table->string('product_name')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('activation_status', [
                'not_called','no_answer','interested','not_ready',
                'callback','ordered_unverified','verified_order','do_not_contact'
            ])->default('not_called');
            $table->string('barrier')->nullable();
            $table->boolean('coupon_offered')->default(false);
            $table->date('follow_up_on')->nullable();
            $table->text('notes')->nullable();
            $table->string('verified_order_id')->nullable();
            $table->timestamp('verified_order_at')->nullable();
            $table->boolean('delivered')->default(false);
            $table->boolean('coupon_redeemed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
