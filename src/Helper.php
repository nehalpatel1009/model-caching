<?php

namespace Nehal\ModelCaching;

use Nehal\ModelCaching\ModelCaching;

class Helper
{
    protected ModelCaching $modelCaching;

    public function __construct(ModelCaching $modelCaching)
    {
        $this->modelCaching = $modelCaching;
    }

    /**
     * Run a closure with caching disabled.
     */
    public function runDisabled(callable $closure)
    {
        return $this->modelCaching->runDisabled($closure);
    }
}
