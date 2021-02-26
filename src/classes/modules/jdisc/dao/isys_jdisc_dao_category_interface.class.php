<?php

/**
 * Used for categories
 *
 * Interface isys_jdisc_dao_category_interface
 */
interface isys_jdisc_dao_category_interface
{
    /**
     * @param int   $deviceId
     * @param bool  $asRaw
     *
     * @param array $deviceToObjectId
     *
     * @param array $idoitObjects
     * @param null  $currentObjectId
     *
     * @return array
     */
    public function getDataForImport(int $deviceId, $asRaw = false, array $deviceToObjectId = [], array $idoitObjects = [], $currentObjectId = null);
}
