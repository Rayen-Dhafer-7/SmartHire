<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerEducation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id');
            $table->string('degree', 200);
            $table->string('institution', 200);
            $table->string('location', 200)->nullable();
            $table->string('start_year', 20)->nullable();
            $table->string('end_year', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index(['worker_id', 'end_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerEducation');
    }
};