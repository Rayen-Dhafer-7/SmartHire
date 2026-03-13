<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerProjects', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id');
            $table->string('project_name', 200);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index(['worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerProjects');
    }
};