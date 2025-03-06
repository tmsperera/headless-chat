<?php

namespace Tmsperera\HeadlessChat\Actions;

use Tmsperera\HeadlessChat\Config\ConfigModels;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Events\MessageSentEvent;
use Tmsperera\HeadlessChat\Exceptions\ParticipantLimitExceededException;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;

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
        return ConfigModels::conversation()::query()
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
        $conversation = ConfigModels::conversation()::query()
            ->create(['type' => ConversationType::DIRECT_MESSAGE]);

        ($this->joinConversation)($sender, $conversation);
        ($this->joinConversation)($recipient, $conversation);

        return $conversation->load('participations');
    }
}
