<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class SendDirectMessageAction
{
    /**
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(
        Participant $sender,
        Participant $recipient,
        string $content,
        array $messageMetadata = [],
    ): Message {
        $conversation = $this->getExistingConversation(sender: $sender, recipient: $recipient)
            ?: HeadlessChat::createConversation(
                participants: [$sender, $recipient],
                conversationType: ConversationType::DIRECT_MESSAGE,
            );

        $participation = $conversation->participations->whereParticipant($sender)->first();

        $message = $participation->messages()->create([
            'conversation_id' => $sender->getKey(),
            'content' => $content,
            'metadata' => $messageMetadata,
        ]);

        MessageSentEvent::dispatch($message);

        return $message;
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return HeadlessChatConfig::conversationInstance()->newQuery()
            ->with('participations')
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }
}
