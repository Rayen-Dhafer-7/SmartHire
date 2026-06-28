<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerCertifications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id');
            $table->string('name', 200);
            $table->string('issuer', 200)->nullable();
            $table->string('issue_date', 50)->nullable();
            $table->timestamps();
            
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index(['worker_id', 'issue_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerCertifications');
    }
};