<?php

namespace Nehal\ModelCaching\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface CacheKeyGeneratorInterface
{
    public function make(Model $model, QueryBuilder $query, array $columns = ['*']): string;
    public function makeTags(Model $model): array;
}
