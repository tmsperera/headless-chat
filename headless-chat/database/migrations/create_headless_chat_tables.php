<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained(table: 'conversations', column: 'id');
            $table->morphs('participant');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')
                ->constrained(table: 'participations', column: 'id');
            $table->foreignId('conversation_id')
                ->constrained(table: 'conversations', column: 'id');
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('messages');
    }
};