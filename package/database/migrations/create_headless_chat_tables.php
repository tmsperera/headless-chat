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
            $table->string('type');
            $table->softDeletes();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained(table: 'conversations', column: 'id');
            $table->morphs('participant');
            $table->timestamps();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'uq_participation');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')
                ->constrained(table: 'participations', column: 'id');
            $table->foreignId('conversation_id')
                ->constrained(table: 'conversations', column: 'id');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('read_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained(table: 'message', column: 'id');
            $table->foreignId('participation_id')
                ->constrained(table: 'participations', column: 'id');
            $table->timestamps();

            $table->unique(['message_id', 'participation_id'], 'uq_read_receipt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('read_receipts');
    }
};
