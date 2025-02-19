<?php 
namespace Nehal\ModelCaching\Traits;

use Nehal\ModelCaching\Traits\PivotEventTrait;

trait Cachable
{
    use Caching;
    use ModelCaching;
    use PivotEventTrait {
        ModelCaching::newBelongsToMany insteadof PivotEventTrait;
    }
}