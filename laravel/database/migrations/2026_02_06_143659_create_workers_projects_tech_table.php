<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerProjectTechnologies', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('project_id');
            $table->string('technology', 100);
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('WorkerProjects')->onDelete('cascade');
            $table->index(['project_id', 'technology']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerProjectTechnologies');
    }
};