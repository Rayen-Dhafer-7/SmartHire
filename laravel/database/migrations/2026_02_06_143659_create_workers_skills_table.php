<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerSkills', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id');
            $table->string('skill_name', 100);
            $table->timestamps();
            
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index(['worker_id', 'skill_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerSkills');
    }
};