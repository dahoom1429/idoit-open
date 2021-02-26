<?php

/**
 * @deprecated Please use "\idoit\AddOn\InstallableInterface".
 * @todo       Remove in i-doit 1.17
 */
interface isys_module_installable
{
    /**
     * @param string $p_identifier
     * @param bool   $p_and_active
     *
     * @return mixed
     * @deprecated
     */
    public function is_installed($p_identifier = null, $p_and_active = false);
}
