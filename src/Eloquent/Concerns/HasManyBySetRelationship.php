<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Concerns;

use Kamicloud\LaravelUnofficialRelations\Eloquent\Relations\HasManyBySet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait HasManyBySetRelationship
{
    /**
     * 关联表，主表逗号外键，关联表键
     * @param $related
     * @param null $foreignKey
     * @param null $relatedKey
     * @return HasManyBySet
     */
    public function hasManyBySet($related, $foreignKey = null, $relatedKey = null)
    {
        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $relatedKey = $relatedKey ?: $instance->getKeyName();


        return $this->newHasManyBySet($instance->newQuery(), $this, $foreignKey, $relatedKey);
    }

    protected function newHasManyBySet(Builder $query, Model $parent, $foreignKey, $relatedKey)
    {
        return new HasManyBySet($query, $parent, $foreignKey, $relatedKey);
    }
}
