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
        Schema::create('translate_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translate_book_id')->index();
            $table->bigInteger('page_index', false);
            $table->bigInteger('page_index_part', false)->default(0);
            $table->longText('original_text');
            $table->longText('translated_text')->nullable();
            $table->text('pre_prompt')->nullable();
            $table->string('last_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translate_pages');
    }
};
