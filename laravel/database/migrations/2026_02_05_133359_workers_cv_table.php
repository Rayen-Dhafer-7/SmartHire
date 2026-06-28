<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WorkerCV', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('worker_id')->unique();
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->integer('file_size')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WorkerCV');
    }
};