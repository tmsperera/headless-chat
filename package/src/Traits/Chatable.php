<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Tmsperera\HeadlessChat\Actions\SendDirectMessageAction;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;

trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(HeadlessChatConfig::participationModelClass(), 'participant');
    }

    public function sendDirectMessageTo(Participant $recipient, string $message): Message
    {
        $sendDirectMessage = App::make(SendDirectMessageAction::class);

        return $sendDirectMessage(sender: $this, recipient: $recipient, message: $message);
    }

    public function getConversations(): Collection
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereForParticipant($this)
            ->get();
    }

    public function getUnreadConversations(): Collection
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereUnreadForParticipant($this)
            ->get();
    }

    public function getUnreadConversationsCount(): int
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereUnreadForParticipant($this)
            ->count();
    }

    //    public function unreadConversationsQuery(): Builder
    //    {
    //        $conversationsTable = HeadlessChatConfig::newConversationModel()->getTable();
    //        $participationsTable = HeadlessChatConfig::newParticipationModel()->getTable();
    //        $messagesTable = HeadlessChatConfig::newMessageModel()->getTable();
    //        $messageReadsTable = HeadlessChatConfig::newMessageReadModel()->getTable();
    //
    //        return $this->participations()
    //            ->select("$conversationsTable.*")
    //            ->selectRaw("COUNT($messagesTable.id) AS unread_message_count")
    //            ->join($conversationsTable, "$conversationsTable.id", '=', "$participationsTable.conversation_id")
    //            ->join($messagesTable, "$messagesTable.conversation_id", '=', "$conversationsTable.id")
    //            ->leftJoin($messageReadsTable, function (JoinClause $join) use ($messageReadsTable, $messagesTable, $participationsTable) {
    //                $join->on("$messageReadsTable.message_id", '=', "$messagesTable.id")
    //                    ->on("$messageReadsTable.participation_id", '=', "$participationsTable.id");
    //            })
    //            ->whereNull("$messageReadsTable.id")
    //            ->groupBy("$conversationsTable.id");
    //    }

    //    public function conversationsQuery(): Builder
    //    {
    //        $conversationsTable = HeadlessChatConfig::newConversationModel()->getTable();
    //        $participationsTable = HeadlessChatConfig::newParticipationModel()->getTable();
    //        $messagesTable = HeadlessChatConfig::newMessageModel()->getTable();
    //        $messageReadsTable = HeadlessChatConfig::newMessageReadModel()->getTable();
    //
    //        return $this->participations()
    //            ->select("$conversationsTable.*")
    //            ->selectRaw("COALESCE(COUNT(CASE WHEN $messageReadsTable.id IS NULL THEN $messagesTable.id END), 0) AS unread_message_count")
    //            ->join($conversationsTable, "$conversationsTable.id", '=', "$participationsTable.conversation_id")
    //            ->leftJoin($messagesTable, "$messagesTable.conversation_id", '=', "$conversationsTable.id")
    //            ->leftJoin($messageReadsTable, function (JoinClause $join) use ($messageReadsTable, $messagesTable, $participationsTable) {
    //                $join->on("$messageReadsTable.message_id", '=', "$messagesTable.id")
    //                    ->on("$messageReadsTable.participation_id", '=', "$participationsTable.id");
    //            })
    //            ->groupBy("$conversationsTable.id");
    //    }
}
