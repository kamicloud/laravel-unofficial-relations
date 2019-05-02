<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;

class HasManyBySetAndMorphToByMap extends MorphTo
{
    protected $map;

    protected $delimiter;

    /**
     * Create a new morph to relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \Illuminate\Database\Eloquent\Model $parent
     * @param  string $foreignKey
     * @param  string $ownerKey
     * @param  string $type
     * @param  string $relation
     * @param  null|array $map
     * @param  string|callable $delimiter
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation, $map = null, $delimiter = ',')
    {
        $this->map = $map;

        $this->delimiter = $delimiter;

        parent::__construct($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $table = $this->related->getTable();

            $this->query->whereIn($table.'.'.$this->ownerKey, $this->child->{$this->foreignKey});
        }
    }

    /**
     * Create a new model instance by type.
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModelByType($type)
    {
        if ($this->map) {
            $class = Arr::get($this->map ?: [], $type, $type);
            return new $class;
        }

        return parent::createModelByType($type);
    }

    /**
     * Match the results for a given type to their parents.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchToMorphParents($type, Collection $results)
    {
        $results = $results->keyBy(! is_null($this->ownerKey) ? $this->ownerKey : $results->last()->getKey());

        foreach ($this->dictionary[$type] as $model) {
            $model->setRelation($this->relation, $results->intersectByKeys(array_flip($this->explodeKey($model->{$this->foreignKey})))->values());
        }
    }

    /**
     * Build a dictionary with the models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $this->dictionary[$model->{$this->morphType}][] = $model;
            }
        }
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }

    /**
     * Get all of the relation results for a type.
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);

        $ownerKey = $this->ownerKey ?? $instance->getKeyName();

        $query = $this->replayMacros($instance->newQuery())
            ->mergeConstraintsFrom($this->getQuery())
            ->with($this->getQuery()->getEagerLoads());

        return $query->whereIn(
            $instance->getTable().'.'.$ownerKey, $this->gatherKeysByType($type)
        )->get();
    }

    /**
     * Gather all of the foreign keys for a given type.
     *
     * @param  string  $type
     * @return array
     */
    protected function gatherKeysByType($type)
    {
        return collect($this->dictionary[$type])->map(function ($model) {
            return $this->explodeKey($model->{$this->foreignKey});
        })->flatten()->unique()->values()->all();
    }

    protected function explodeKey($key)
    {
        if (is_string($this->delimiter) && is_string($key)) {
            return array_values(array_filter(explode($this->delimiter, $key)));
        } elseif (is_callable($this->delimiter)) {
            $func = $this->delimiter;
            return array_values(array_filter($func($key)));
        } else {
            return $key;
        }
    }
}
