<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

readonly class MessageDto
{
    public function __construct(
        public string $type,
        public string $content,
        public array $metadata = [],
    ) {}
}
