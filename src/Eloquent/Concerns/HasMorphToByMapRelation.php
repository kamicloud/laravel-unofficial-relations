<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Concerns;

use Kamicloud\LaravelUnofficialRelations\Eloquent\Relations\MorphToByMap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasMorphToByMapRelation
{
    /**
     * Can load morphTo with customize map
     *
     * @param  string  $name
     * @param  string  $type Same as MorphTo
     * @param  string  $id Same as MorphTo
     * @param  string  $ownerKey Same as MorphTo
     * @param  array|null $map [Morph type key name in DB => Model namespace]
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function morphToByMap($name = null, $type = null, $id = null, $ownerKey = null, $map = null)
    {
        // If no name is provided, we will use the backtrace to get the function name
        // since that is most likely the name of the polymorphic interface. We can
        // use that to get both the class and foreign key that will be utilized.
        $name = $name ?: $this->guessBelongsToRelation();

        [$type, $id] = $this->getMorphs(
            Str::snake($name), $type, $id
        );

        // If the type value is null it is probably safe to assume we're eager loading
        // the relationship. In this case we'll just pass in a dummy query where we
        // need to remove any eager loads that may already be defined on a model.
        return empty($class = $this->{$type})
            ? $this->morphEagerToByMap($name, $type, $id, $ownerKey, $map)
            : $this->morphInstanceToByMap($class, $name, $type, $id, $ownerKey, $map);
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @param  string  $ownerKey
     * @param  array|null $map
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function morphEagerToByMap($name, $type, $id, $ownerKey, $map)
    {
        return $this->newMorphToByMap(
            $this->newQuery()->setEagerLoads([]), $this, $id, $ownerKey, $type, $name, $map
        );
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string  $target
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @param  string  $ownerKey
     * @param  array|null $map
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function morphInstanceToByMap($target, $name, $type, $id, $ownerKey, $map)
    {
        $instance = $this->newRelatedInstance(
            static::getActualClassNameForMorph($target)
        );

        return $this->newMorphToByMap(
            $instance->newQuery(), $this, $id, $ownerKey ?? $instance->getKeyName(), $type, $name, $map
        );
    }

    /**
     * Instantiate a new MorphTo relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $type
     * @param  string  $relation
     * @param  array|null $map
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function newMorphToByMap(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation, $map)
    {
        return new MorphToByMap($query, $parent, $foreignKey, $ownerKey, $type, $relation, $map);
    }
}
