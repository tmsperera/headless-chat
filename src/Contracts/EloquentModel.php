<?php

namespace TMSPerera\HeadlessChat\Contracts;

interface EloquentModel
{
    public function getKey();

    public function getMorphClass();

    public function is($model);
}
