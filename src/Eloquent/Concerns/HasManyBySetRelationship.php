<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Concerns;

use Kamicloud\LaravelUnofficialRelations\Eloquent\Relations\HasManyBySet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait HasManyBySetRelationship
{
    /**
     * Can load relation by id array or string
     *
     * @param $related
     * @param string|null $foreignKey Format id1, id2 | array<id>
     * @param string|null $relatedKey
     * @param string|callable $delimiter Default ',', allows customize function or null(for array foreignKey)
     * @return HasManyBySet
     */
    public function hasManyBySet($related, $foreignKey = null, $relatedKey = null, $delimiter = ',')
    {
        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $relatedKey = $relatedKey ?: $instance->getKeyName();


        return $this->newHasManyBySet($instance->newQuery(), $this, $foreignKey, $relatedKey, $delimiter);
    }

    protected function newHasManyBySet(Builder $query, Model $parent, $foreignKey, $relatedKey, $delimiter)
    {
        return new HasManyBySet($query, $parent, $foreignKey, $relatedKey, $delimiter);
    }
}
