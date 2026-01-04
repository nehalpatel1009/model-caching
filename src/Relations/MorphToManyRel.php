<?php

namespace Nehal\ModelCaching\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nehal\ModelCaching\Traits\FiresPivotEventsTrait;

class MorphToManyRel extends MorphToMany
{
    use FiresPivotEventsTrait;
}