<?php

namespace Tmsperera\HeadlessChat\Usecases;

use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Exceptions\ParticipantLimitExceededException;
use Tmsperera\HeadlessChat\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;

class SendDirectMessage
{
    public function __construct(
        protected JoinConversation $joinConversation,
    ) {}

    /**
     * @throws ParticipantLimitExceededException
     */
    public function __invoke(Participant $sender, Participant $recipient, string $content): Message
    {
        $conversation = $this->getExistingConversation($sender, $recipient)
            ?: $this->createConversation($sender, $recipient);

        $participation = $conversation->participations->whereParticipant($sender);

        return $participation->messages()->create([
            'conversation_id' => $sender->getKey(),
            'content' => $content,
        ]);
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->with('participations')
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }

    /**
     * @throws ParticipantLimitExceededException
     */
    protected function createConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        $conversation = HeadlessChatConfig::conversationModelClass()::query()
            ->create(['type' => ConversationType::DIRECT_MESSAGE]);

        ($this->joinConversation)($sender, $conversation);
        ($this->joinConversation)($recipient, $conversation);

        return $conversation->load('participations');
    }
}
