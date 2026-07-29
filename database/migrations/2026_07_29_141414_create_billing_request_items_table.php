<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_request_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 32);
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalog')->nullOnDelete();
            $table->foreignId('part_catalog_id')->nullable()->constrained('part_catalog')->nullOnDelete();
            $table->string('code', 50)->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('billing_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_request_items');
    }
};
