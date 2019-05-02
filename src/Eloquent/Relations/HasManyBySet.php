<?php

namespace Kamicloud\LaravelUnofficialRelations\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HasManyBySet extends Relation
{
    protected $relatedKey;

    protected $foreignKey;

    protected $parentKey;

    protected $delimiter;

    public function __construct(Builder $query, Model $parent, $foreignKey, $relatedKey, $delimiter = ',')
    {
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
        $this->delimiter = $delimiter;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $whereIn = $this->whereInMethod($this->parent, $this->foreignKey);

        if (static::$constraints) {
            $this->query->{$whereIn}($this->relatedKey, $this->explodeKey($this->parent->{$this->foreignKey}));
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->parent, $this->foreignKey);
        $keys = $this->getKeys($models, $this->foreignKey);

        $this->query->{$whereIn}($this->relatedKey, array_flatten(array_map(function ($keys) {
            return $this->explodeKey($keys);
        }, $keys)));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array $models
     * @param  string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $keys = array_flip($this->explodeKey($model->{$this->foreignKey}));

            $model->setRelation(
                $relation, $this->related->newCollection(array_values(array_intersect_key($dictionary, $keys)))
            );
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        // First we will build a dictionary of child models keyed by the foreign key
        // of the relation so that we will easily and quickly match them to their
        // parents without having a possibly slow inner loops for every models.
        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return ! is_null($this->parent->{$this->foreignKey})
            ? $this->get()
            : $this->related->newCollection();
    }

    protected function explodeKey($key)
    {
        if (is_string($this->delimiter)) {
            return array_values(array_filter(explode($this->delimiter, $key)));
        } elseif (is_callable($this->delimiter)) {
            return array_values(array_filter(array_map($this->delimiter, $key)));
        } else {
            return $key;
        }
    }
}
