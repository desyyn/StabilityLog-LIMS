<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parameter_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('testing_parameters')->cascadeOnDelete();
            $table->double('min_value')->nullable();
            $table->double('max_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parameter_limits');
    }
};
