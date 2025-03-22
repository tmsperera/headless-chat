<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;

return new class extends Migration
{
    public function up(): void
    {
        $conversation = HeadlessChatConfig::conversationModel();
        $participation = HeadlessChatConfig::participationModel();
        $message = HeadlessChatConfig::messageModel();
        $readReceipt = HeadlessChatConfig::readReceiptModel();

        Schema::create($conversation->getTable(), function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('type');
            $table->json('metadata')->default(json_encode([]));
            $table->softDeletes();
        });

        Schema::create($participation->getTable(), function (Blueprint $table) use ($participation, $conversation) {
            $table->id();
            $table->foreignId($participation->conversation()->getForeignKeyName())
                ->constrained(
                    table: $conversation->getTable(),
                    column: $conversation->getKeyName()
                );
            $table->morphs($participation->participant()->getRelationName());
            $table->json('metadata')->default(json_encode([]));
            $table->timestamps();

            $table->unique([
                $participation->conversation()->getForeignKeyName(),
                $participation->participant()->getMorphType(),
                $participation->participant()->getForeignKeyName(),
            ], 'uq_participation');
        });

        Schema::create($message->getTable(), function (Blueprint $table) use ($message, $participation, $conversation) {
            $table->id();
            $table->foreignId($message->participation()->getForeignKeyName())
                ->constrained(
                    table: $participation->getTable(),
                    column: $participation->getKeyName(),
                );
            $table->foreignId($message->conversation()->getForeignKeyName())
                ->constrained(
                    table: $conversation->getTable(),
                    column: $conversation->getKeyName(),
                );
            $table->text('content');
            $table->json('metadata')->default(json_encode([]));
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($readReceipt->getTable(), function (Blueprint $table) use ($readReceipt, $message, $conversation) {
            $table->id();
            $table->foreignId($readReceipt->message()->getForeignKeyName())
                ->constrained(
                    table: $message->getTable(),
                    column: $message->getKeyName()
                );
            $table->foreignId($readReceipt->participation()->getForeignKeyName())
                ->constrained(
                    table: $conversation,
                    column: $conversation->getKeyName()
                );
            $table->timestamps();

            $table->unique([
                $readReceipt->message()->getForeignKeyName(),
                $readReceipt->participation()->getForeignKeyName(),
            ], 'uq_read_receipt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(HeadlessChatConfig::readReceiptModel()->getTable());
        Schema::dropIfExists(HeadlessChatConfig::messageModel()->getTable());
        Schema::dropIfExists(HeadlessChatConfig::participationModel()->getTable());
        Schema::dropIfExists(HeadlessChatConfig::conversationModel()->getTable());
    }
};
