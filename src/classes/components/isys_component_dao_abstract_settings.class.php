<?php

/**
 * i-doit
 *
 * Settings DAO.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_component_dao_abstract_settings extends isys_component_dao
{
    /**
     * @var array
     */
    protected $m_cached_settings = null;

    /**
     * @param $p_key
     * @param $p_value
     *
     * @return mixed
     */
    abstract public function set($p_key, $p_value);

    /**
     * Remove setting
     *
     * @param $p_key
     *
     * @return mixed
     */
    abstract public function remove($p_key);

    /**
     * Save settings.
     *
     * @param   array $p_settings
     *
     * @return  boolean
     */
    public function save($p_settings)
    {
        if (is_array($p_settings) && count($p_settings) > 0) {
            $this->begin_update();

            foreach ($p_settings as $l_key => $l_value) {
                if ($l_key) {
                    if ($l_value === true) {
                        $l_value = '1';
                    }

                    if ($l_value === false) {
                        $l_value = '0';
                    }

                    $this->set($l_key, $l_value);
                }
            }

            return $this->apply_update();
        }

        return false;
    }
}