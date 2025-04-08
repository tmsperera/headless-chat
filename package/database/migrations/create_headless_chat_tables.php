<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const CONVERSATIONS_TABLE = 'conversations';

    const PARTICIPATIONS_TABLE = 'participations';

    const MESSAGES_TABLE = 'messages';

    const READ_RECEIPTS_TABLE = 'read_receipts';

    public function up(): void
    {
        Schema::create(self::CONVERSATIONS_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(self::PARTICIPATIONS_TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained(table: static::CONVERSATIONS_TABLE, column: 'id');
            $table->morphs('participant');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'uq_participation');
        });

        Schema::create(self::MESSAGES_TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')
                ->constrained(table: static::PARTICIPATIONS_TABLE, column: 'id');
            $table->foreignId('conversation_id')
                ->constrained(table: static::CONVERSATIONS_TABLE, column: 'id');
            $table->foreignId('parent_id')->nullable()
                ->constrained(table: static::MESSAGES_TABLE, column: 'id');
            $table->string('type');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(self::READ_RECEIPTS_TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained(table: static::MESSAGES_TABLE, column: 'id');
            $table->foreignId('participation_id')
                ->constrained(table: static::PARTICIPATIONS_TABLE, column: 'id');
            $table->timestamps();

            $table->unique(['message_id', 'participation_id'], 'uq_read_receipt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::READ_RECEIPTS_TABLE);
        Schema::dropIfExists(self::MESSAGES_TABLE);
        Schema::dropIfExists(self::PARTICIPATIONS_TABLE);
        Schema::dropIfExists(self::CONVERSATIONS_TABLE);
    }
};
