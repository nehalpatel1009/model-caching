<?php

namespace Nehal\ModelCaching;

use Nehal\ModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

abstract class CachedModel extends Model
{
    use Cachable;
}
