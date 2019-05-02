<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Concerns;

use Kamicloud\LaravelUnofficialRelations\Eloquent\Relations\HasManyBySetAndMorphToByMap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasManyBySetAndMorphToByMapRelation
{
    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string $name
     * @param  string $type
     * @param  string $id
     * @param  string $ownerKey
     * @param  array|null $map
     * @param  string|callable $delimiter
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function hasManyBySetAndMorphToByMap($name = null, $type = null, $id = null, $ownerKey = null, $map = null, $delimiter = ',')
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
            ? $this->hasManyBySetAndMorphEagerToByMap($name, $type, $id, $ownerKey, $map, $delimiter)
            : $this->hasInstancesBySetAndMorphToByMap($class, $name, $type, $id, $ownerKey, $map, $delimiter);
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @param  string  $ownerKey
     * @param  array|null $map
     * @param  string|callable $delimiter
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function hasManyBySetAndMorphEagerToByMap($name, $type, $id, $ownerKey, $map, $delimiter)
    {
        return $this->newHasManyBySetAndMorphToByMap(
            $this->newQuery()->setEagerLoads([]), $this, $id, $ownerKey, $type, $name, $map, $delimiter
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
     * @param  string|callable $delimiter
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function hasInstancesBySetAndMorphToByMap($target, $name, $type, $id, $ownerKey, $map, $delimiter)
    {
        if ($map) {
            $class = Arr::get($map ?: [], $target, $target);
            $instance = new $class;
        } else {
            $instance = $this->newRelatedInstance(
                static::getActualClassNameForMorph($target)
            );
        }

        return $this->newHasManyBySetAndMorphToByMap(
            $instance->newQuery(), $this, $id, $ownerKey ?? $instance->getKeyName(), $type, $name, $map, $delimiter
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
     * @param  string|callable $delimiter
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function newHasManyBySetAndMorphToByMap(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation, $map, $delimiter)
    {
        return new HasManyBySetAndMorphToByMap($query, $parent, $foreignKey, $ownerKey, $type, $relation, $map, $delimiter);
    }
}
