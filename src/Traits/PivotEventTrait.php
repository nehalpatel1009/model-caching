<?php
namespace Nehal\ModelCaching\Traits;

trait PivotEventTrait
{
    use ExtendRelationsTrait;
    use ExtendFireModelEventTrait;

    /**
     * Get the observable event names.
     *
     * @return array
     */
    public function getObservableEvents()
    {
        return array_merge(
            parent::getObservableEvents(),
            [
                'pivotSyncing',
            ],
            $this->observables
        );
    }

    public static function pivotSyncing($callback, $priority = 0)
    {
        static::registerModelEvent('pivotSyncing', $callback, $priority);
    }
}
