<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

class MessageDto
{
    public function __construct(
        readonly public string $type,
        readonly public string $content,
        readonly public array $metadata = [],
    ) {}
}
