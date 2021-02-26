<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

/**
 * Interface SynchronizerInterface
 *
 * @package idoit\Module\Multiedit\Model
 */
interface SynchronizerInterface
{
    /**
     * @return mixed
     */
    public function mapSyncData();

    /**
     * @return mixed
     */
    public function synchronize();
}
