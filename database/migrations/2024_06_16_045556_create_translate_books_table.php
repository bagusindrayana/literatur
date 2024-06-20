<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translate_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id')->index();
            $table->string('from_language');
            $table->string('to_language');
            $table->string('provider',100);
            $table->text('pre_prompt')->nullable();
            $table->string('api_key',150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translate_books');
    }
};
