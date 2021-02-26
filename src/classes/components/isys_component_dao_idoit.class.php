<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_idoit extends isys_component_dao
{
    /**
     * This array will be filled with the "isys_db_init" data.
     *
     * @var  null
     */
    private $m_isys_info = null;

    /**
     * Method for loading the data from "isys_db_init".
     *
     * @return  array
     */
    public function get_info()
    {
        if ($this->m_isys_info === null) {
            $l_title = $l_version = $l_revision = '';

            $l_res = $this->retrieve('SELECT * FROM isys_db_init;');

            while ($l_row = $l_res->get_row()) {
                if ($l_row['isys_db_init__key'] === 'version') {
                    $l_version = $l_row['isys_db_init__value'];
                }

                if ($l_row['isys_db_init__key'] === 'revision') {
                    $l_revision = $l_row['isys_db_init__value'];
                }

                if ($l_row['isys_db_init__key'] === 'title') {
                    $l_title = $l_row['isys_db_init__value'];
                }
            }

            $this->m_isys_info = [
                'name'     => $l_title,
                'version'  => $l_version,
                'revision' => $l_revision
            ];
        }

        return $this->m_isys_info;
    }

    /**
     * Method for retrieving the revision number.
     *
     * @return  string
     */
    public function get_revision()
    {
        return $this->get_info()['revision'];
    }

    /**
     * Method for retrieving the version name.
     *
     * @return  string
     */
    public function get_version_name()
    {
        return $this->get_info()['name'];
    }

    /**
     * Method for retrieving the version number.
     *
     * @return  string
     */
    public function get_version()
    {
        return $this->get_info()['version'];
    }
}
