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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['to-do', 'doing', 'done', 'failed', 'reworking'])->default('to-do');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('start_time')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
            $table->timestamp('end_time')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
