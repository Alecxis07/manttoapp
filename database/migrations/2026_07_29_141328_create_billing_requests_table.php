<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('maintenance_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_fiscal_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->json('fiscal_profile_snapshot');
            $table->string('status', 32)->default('draft');
            $table->string('payment_method_code', 10)->nullable();
            $table->string('payment_form_code', 10)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(16.00);
            $table->string('invoice_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_id');
            $table->index('maintenance_order_id');
            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_requests');
    }
};
