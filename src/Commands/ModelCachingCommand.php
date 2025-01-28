<?php

namespace Nehal\ModelCaching\Commands;

use Illuminate\Console\Command;

class ModelCachingCommand extends Command
{
    public $signature = 'model-caching';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
