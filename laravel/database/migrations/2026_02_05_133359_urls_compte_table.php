<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('UrlsCompte', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->enum('user_type', ['worker', 'company']);
            $table->string('url_github', 255)->nullable();
            $table->string('url_linkedin', 255)->nullable();
            $table->string('url_facebook', 255)->nullable();
            $table->string('url_instagram', 255)->nullable();
            $table->string('url_twitter', 255)->nullable();
            $table->string('url_website', 255)->nullable();
            $table->string('url_gmail', 255)->nullable();
            
            $table->index(['user_id', 'user_type'], 'idx_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('UrlsCompte');
    }
};