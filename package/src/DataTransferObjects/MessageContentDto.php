<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

readonly class MessageContentDto
{
    public function __construct(
        public string $type,
        public string $content,
        public array $metadata = [],
    ) {}
}
