# laravel-unoffical-relations

This package provides several unofficial-relations to help laravel developer load models easily.

## Available relations

### HasManyBySet

````
* Can load relation by id array or string
*
* @param $related
* @param string|null $foreignKey Format id1, id2 | array<id>
* @param string|null $relatedKey
* @param string|callable $delimiter Default ',', allows customize function or null(for array foreignKey)
````

### HasMorphToByMapRelation

````
* Can load morphTo with customize map
*
* @param  string  $name
* @param  string  $type Same as MorphTo
* @param  string  $id Same as MorphTo
* @param  string  $ownerKey Same as MorphTo
* @param  array|null $map [Morph type key name in DB => Model namespace]
````

### HasManyBySetAndMorphToByMapRelation

````
* Can load morphTo with customize map and id can be a set
*
* @param  string  $name
* @param  string  $type Same as MorphTo
* @param  string  $id Same as MorphTo
* @param  string  $ownerKey Same as MorphTo
* @param  array|null $map Same as HasMorphToByMapRelation
* @param  string|callable $delimiter Same as HasManyBySetRelationship
````