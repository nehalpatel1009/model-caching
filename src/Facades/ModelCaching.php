<?php

namespace Nehal\ModelCaching\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nehal\ModelCaching\ModelCaching
 */
class ModelCaching extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nehal\ModelCaching\ModelCaching::class;
    }
}
