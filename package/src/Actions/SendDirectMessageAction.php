<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Exceptions\ParticipantLimitExceededException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class SendDirectMessageAction
{
    public function __construct(
        protected JoinConversationAction $joinConversation,
    ) {}

    /**
     * @throws ParticipantLimitExceededException
     */
    public function __invoke(Participant $sender, Participant $recipient, string $content): Message
    {
        $conversation = $this->getExistingConversation($sender, $recipient)
            ?: $this->createConversation($sender, $recipient);

        $participation = $conversation->participations->whereParticipant($sender);

        $message = $participation->messages()->create([
            'conversation_id' => $sender->getKey(),
            'content' => $content,
        ]);

        MessageSentEvent::dispatch($message);

        return $message;
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
