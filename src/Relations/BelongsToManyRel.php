<?php 
namespace Nehal\ModelCaching\Traits\Relations;

use Nehal\ModelCaching\Traits\Traits\FiresPivotEventsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyRel extends BelongsToMany
{
    use FiresPivotEventsTrait;
}