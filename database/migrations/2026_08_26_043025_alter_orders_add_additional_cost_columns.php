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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('service_charge_rate', 'additional_cost_rate');
            $table->renameColumn('service_charge', 'additional_cost');
            $table->foreignId('additional_cost_id')->nullable()->after('discount')->constrained('additional_costs')->nullOnDelete();
            $table->string('additional_cost_name')->nullable()->after('additional_cost_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['additional_cost_id']);
            $table->dropColumn(['additional_cost_id', 'additional_cost_name']);
            $table->renameColumn('additional_cost_rate', 'service_charge_rate');
            $table->renameColumn('additional_cost', 'service_charge');
        });
    }
};
