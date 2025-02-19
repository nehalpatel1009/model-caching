<?php
namespace Nehal\ModelCaching\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

trait FiresPivotEventsTrait
{
    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param mixed $ids
     * @param bool $detaching
     *
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        if (false === $this->parent->fireModelEvent('pivotSyncing', true, $this->getRelationName())) {
            return false;
        }

        $parentResult = [];
        $this->parent->withoutEvents(function () use ($ids, $detaching, &$parentResult) {
            $parentResult = parent::sync($ids, $detaching);
        });

        $this->parent->fireModelEvent('pivotSynced', false, $this->getRelationName(), $parentResult);

        return $parentResult;
    }
}