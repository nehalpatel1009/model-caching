<?php

namespace Nehal\ModelCaching\Relations;

use Nehal\ModelCaching\Traits\FiresPivotEventsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyRel extends BelongsToMany
{
    use FiresPivotEventsTrait;
}