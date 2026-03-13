<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerExperience', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id');
            $table->string('title', 200);
            $table->string('company', 200);
            $table->string('location', 200)->nullable();
            $table->enum('employment_type', ['Onsite', 'Remote', 'Hybrid'])->default('Onsite');
            $table->string('start_date', 50)->nullable();
            $table->string('end_date', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index(['worker_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerExperience');
    }
};