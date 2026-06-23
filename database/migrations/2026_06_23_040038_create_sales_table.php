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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('report_date'); // Para filtrar por mes y año (usaremos el primer día del mes del reporte)
            $table->string('client_code')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_class')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('total_sales', 20, 2)->default(0.00); // Monto en Bs
            $table->decimal('total_cost', 20, 2)->default(0.00);
            $table->decimal('total_utility', 20, 2)->default(0.00);
            $table->decimal('utility_percentage', 8, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

