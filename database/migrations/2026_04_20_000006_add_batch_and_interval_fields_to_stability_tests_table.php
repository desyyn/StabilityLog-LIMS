<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stability_tests', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('batches')->nullOnDelete();
            $table->enum('interval_type', ['days', 'months'])->default('days')->after('schedule_date');
            $table->unsignedInteger('interval_value')->nullable()->after('interval_type');
        });
    }

    public function down(): void
    {
        Schema::table('stability_tests', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn(['batch_id', 'interval_type', 'interval_value']);
        });
    }
};
