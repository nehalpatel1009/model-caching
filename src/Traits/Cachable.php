<?php

namespace Nehal\ModelCaching\Traits;

use Nehal\ModelCaching\Traits\CachesQueries;
use Nehal\ModelCaching\Traits\PivotEventTrait;

trait Cachable
{
    use CachesQueries;
    use PivotEventTrait;
}