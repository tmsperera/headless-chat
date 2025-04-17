<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\LazyLoadingViolationException;

trait EnsuresEagerLoaded
{
    public function ensureRelationLoaded(string $relation): void
    {
        [$childRelation, $descendantRelation] = array_replace(
            [null, null],
            explode('.', $relation, 2),
        );

        if (! $this->relationLoaded($childRelation)) {
            throw new LazyLoadingViolationException(model: $this, relation: $childRelation);
        }

        if ($descendantRelation) {
            foreach ($this->$childRelation as $related) {
                $related->ensureRelationLoaded($descendantRelation);
            }
        }
    }
}