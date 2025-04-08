<?php

namespace TMSPerera\HeadlessChat\Actions;

use Illuminate\Support\Facades\DB;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Facades\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;

class CreateConversationAction
{
    /**
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(
        array $participants,
        ConversationDto $conversationDto,
    ): Conversation {
        $this->validate(participants: $participants, conversationDto: $conversationDto);

        return DB::transaction(function () use ($participants, $conversationDto) {
            /** @var Conversation $conversation */
            $conversation = HeadlessChat::config()->conversationModel()->newQuery()
                ->create([
                    'type' => $conversationDto->conversationType,
                    'metadata' => $conversationDto->metadata,
                ]);

            $participations = array_map(function (Participant $participant) {
                return [
                    'participant_type' => $participant->getMorphClass(),
                    'participant_id' => $participant->getKey(),
                ];
            }, $participants);

            $conversation->participations()->createMany($participations);

            return $conversation;
        });
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    protected function validate(array $participants, ConversationDto $conversationDto): void
    {
        if (
            $conversationDto->conversationType === ConversationType::DIRECT_MESSAGE
            && count($participants) > 2
        ) {
            throw new ParticipationLimitExceededException;
        }
    }
}
