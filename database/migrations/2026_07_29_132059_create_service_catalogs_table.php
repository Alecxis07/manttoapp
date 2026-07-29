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
        Schema::create('service_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description');
            $table->foreignId('service_category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('type', 32)->index();
            $table->decimal('base_price', 12, 2);
            $table->string('unit_of_measure', 50);
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_catalog');
    }
};
