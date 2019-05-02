<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;

class MorphToByMap extends MorphTo
{
    protected $map;

    /**
     * Create a new morph to relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $type
     * @param  string  $relation
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation, $map = null)
    {
        $this->map = $map;

        parent::__construct($query, $parent, $foreignKey, $ownerKey, $type, $relation);
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
}
