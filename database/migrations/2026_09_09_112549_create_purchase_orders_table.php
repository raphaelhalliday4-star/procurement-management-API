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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('po_number')->unique();
            $table->text('delivery_address')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->text('payment_terms');
            $table->decimal('tax', 15, 2)->default(0); $table->decimal('discount', 15, 2)->default(0); $table->decimal('total_amount', 15, 2)->default(0); 
            $table->enum('status', [ 'draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'received', 'cancelled' ])->default('draft');
            $table->foreignId('created_by') ->nullable() ->constrained('users') ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
