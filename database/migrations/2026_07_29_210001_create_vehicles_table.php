<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->restrictOnDelete();
            $table->string('license_plate');
            $table->string('license_plate_normalized')->unique();
            $table->string('vin', 32)->nullable()->unique();
            $table->string('economic_number', 50)->nullable();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('engine_type')->nullable();
            $table->unsignedInteger('current_mileage')->default(0);
            $table->string('status', 32)->default('active');
            $table->text('status_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['customer_id', 'economic_number']);
            $table->index(['customer_id', 'status']);
            $table->index('brand');
            $table->index('model');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
