<?php

namespace Tmsperera\HeadlessChat\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;

class ConversationBuilder extends Builder
{
    public function whereDirectMessage(): static
    {
        return $this->where('type', ConversationType::DIRECT_MESSAGE);
    }

    public function whereHasParticipant(Participant $participant): static
    {
        return $this->whereHas('participations', function (Builder $query) use ($participant) {
            $query->whereMorphedTo('participant', $participant);
        });
    }

    public function whereForParticipant(Participant $participant): static
    {
        $conversationsTable = HeadlessChatConfig::conversationModel()->getTable();
        $participationsTable = HeadlessChatConfig::participationModel()->getTable();
        $messagesTable = HeadlessChatConfig::messageModel()->getTable();
        $readReceiptsTable = HeadlessChatConfig::readReceiptModel()->getTable();

        return $this
            ->select("$conversationsTable.*")
            ->selectRaw("COUNT($messagesTable.id) AS total_message_count")
            ->selectRaw("COUNT($readReceiptsTable.id) AS read_message_count")
            ->selectRaw("COUNT($messagesTable.id) - COUNT($readReceiptsTable.id) AS unread_message_count")
            ->selectRaw("MAX($messagesTable.created_at) AS latest_message_at")
            ->join($participationsTable, function (JoinClause $join) use ($participationsTable, $conversationsTable, $participant) {
                $join->on("$participationsTable.conversation_id", '=', "$conversationsTable.id")
                    ->on("$participationsTable.participant_id", '=', $participant->getKey())
                    ->on("$participationsTable.participant_type", '=', $participant->getMorphClass());
            })
            ->leftJoin($messagesTable, "$messagesTable.conversation_id", '=', "$conversationsTable.id")
            ->leftJoin($readReceiptsTable, function (JoinClause $join) use ($readReceiptsTable, $messagesTable, $participationsTable) {
                $join->on("$readReceiptsTable.message_id", '=', "$messagesTable.id")
                    ->on("$readReceiptsTable.participation_id", '=', "$participationsTable.id");
            })
            ->groupBy("$conversationsTable.id");
    }
}
