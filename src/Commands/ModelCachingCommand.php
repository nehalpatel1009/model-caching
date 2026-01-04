<?php

namespace Nehal\ModelCaching\Commands;

use Illuminate\Console\Command;
use Nehal\ModelCaching\ModelCaching;

class ClearCacheCommand extends Command
{
    protected $signature = 'model-cache:clear {--model= : The model class to clear cache for}';

    protected $description = 'Clear model cache for a specific model or all models';

    protected ModelCaching $modelCaching;

    public function __construct(ModelCaching $modelCaching)
    {
        parent::__construct();
        $this->modelCaching = $modelCaching;
    }

    public function handle(): int
    {
        $model = $this->option('model');

        if ($model) {
            if (!class_exists($model)) {
                $this->error("Model class '{$model}' does not exist.");
                return self::FAILURE;
            }

            if ($this->modelCaching->clearModel($model)) {
                $this->info("Cache cleared for model: {$model}");
                return self::SUCCESS;
            }

            $this->error("Failed to clear cache for model: {$model}");
            return self::FAILURE;
        }

        if ($this->modelCaching->clearAll()) {
            $this->info('All model caches cleared successfully.');
            return self::SUCCESS;
        }

        $this->error('Failed to clear model caches.');
        return self::FAILURE;
    }
}
