<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testing_parameters', function (Blueprint $table) {
            $table->enum('type', ['numeric', 'organoleptic'])->default('numeric')->after('param_name');
            $table->string('unit')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('testing_parameters', function (Blueprint $table) {
            $table->dropColumn(['type', 'unit']);
        });
    }
};
