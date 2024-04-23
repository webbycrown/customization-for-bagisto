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
        Schema::create('customization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug')->nullable();
            $table->string('section_slug')->nullable();
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->integer('required')->default(0)->nullable();
            $table->integer('multiple')->default(0)->nullable();
            $table->integer('status')->default(0)->nullable();
            $table->integer('parent_id')->default(0)->nullable();
            $table->string('setting_type')->nullable();
            $table->json('other_settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customization_settings');
    }
};
