<?php

namespace Tests\Feature\Chatable;

use Tests\TestCase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\ReadReceiptFactory;

abstract class BaseChatableTestCase extends TestCase
{
    protected function joinConversation(Conversation $conversation, Participant $participant): Participation
    {
        return ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($participant)
            ->createOne();
    }

    protected function sendMessageFromParticipant(Participant $sender, Conversation $conversation): Message
    {
        $sender->refresh();

        $participation = $sender->participations
            ->where('conversation_id', $conversation->getKey())
            ->firstOrFail();

        return $this->sendMessage(senderParticipation: $participation, conversation: $conversation);
    }

    protected function sendMessage(
        Participation $senderParticipation,
        Conversation $conversation,
    ): Message {
        return MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->createOne();
    }

    protected function readMessage(
        Participation $readerParticipation,
        Message $message,
    ): ReadReceipt {
        return ReadReceiptFactory::new()
            ->forParticipation($readerParticipation)
            ->forMessage($message)
            ->createOne();
    }
}
