<?php

namespace idoit\Console\Command\Import\Ocs;

use idoit\Component\ContainerFacade;
use idoit\Console\Command\IsysLogWrapper;
use isys_cmdb_dao_category;
use isys_event_manager;
use isys_module_logbook;

abstract class AbstractOcs
{
    /**
     * @var ContainerFacade
     */
    protected $container;

    /**
     * @var
     */
    protected $matcher;

    /**
     * @var isys_module_logbook
     */
    protected $logbook;

    /**
     * @var IsysLogWrapper
     */
    protected $logger;

    /**
     * @return OcsMatcher
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * @param OcsMatcher $matcher
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setMatcher($matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * @return isys_module_logbook
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getLogbook()
    {
        return $this->logbook;
    }

    /**
     * @param isys_module_logbook $logbook
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setLogbook($logbook)
    {
        $this->logbook = $logbook;
    }

    /**
     * @return IsysLogWrapper
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param IsysLogWrapper $logger
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ContainerFacade $container
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Check if pattern prefix exists in TAG or in NAME
     *
     * @param $p_prefix
     * @param $p_tag
     * @param $p_name
     *
     * @return bool
     */
    public function check_pattern_for_objtype($p_prefix, $p_tag, $p_name)
    {
        if (strpos($p_prefix, '%') !== false) {
            $l_search_string = '';
            $l_pattern = '';
            $p_prefix = preg_quote($p_prefix);
            if (strpos($p_prefix, '%') > 0) {
                $l_search_string = '^';
            }

            $l_search_string .= '(' . str_replace('%', '.*', trim($p_prefix, '%')) . ')';
            $l_pattern .= "/" . $l_search_string . "/";
            if (preg_match($l_pattern, $p_tag) !== 0 || preg_match($l_pattern, $p_name) !== 0) {
                return true;
            }
        } elseif (strpos($p_tag, $p_prefix) === 0 || strpos($p_name, $p_prefix) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Parse the storage type to determine the corresponding storage type in i-doit
     *
     * @param String $p_strType The String to be parsed
     *
     * @return int The ID of the corresponding storage type, if applicable, else false
     */
    public function parseStorageType($p_strType)
    {
        $l_storageTypes = filter_array_by_value_of_defined_constants([
            "disk"                  => 'C__STOR_TYPE_DEVICE_HD',
            "Fixedxhard disk media" => 'C__STOR_TYPE_DEVICE_HD',
            "cdrom"                 => 'C__STOR_TYPE_DEVICE_CD_ROM',
            "CD-ROM"                => 'C__STOR_TYPE_DEVICE_CD_ROM',
            "CD-ROM-Laufwerk"       => 'C__STOR_TYPE_DEVICE_CD_ROM',
            "Diskettenlaufwerk"     => 'C__STOR_TYPE_DEVICE_FLOPPY'
        ]);

        return isset($l_storageTypes[$p_strType]) ? $l_storageTypes[$p_strType] : null;
    }

    /**
     * Deletes entries from category
     *
     * @param array                  $p_arr
     * @param isys_cmdb_dao_category $p_dao
     * @param string                 $p_table
     * @param int                    $p_obj_id
     * @param int                    $p_obj_type
     * @param string                 $p_category_title
     * @param boolean                $p_logb_active
     *
     * @return null
     */
    public function delete_entries_from_category(array $p_arr, isys_cmdb_dao_category $p_dao, $p_table, $p_obj_id, $p_obj_type, $p_category_title, $p_logb_active)
    {
        if (empty($p_table)) {
            return null;
        }

        foreach ($p_arr AS $l_val) {
            $p_dao->delete_entry($l_val['data_id'], $p_table);
            if ($p_logb_active) {
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_PURGED', "-modified from OCS-", $p_obj_id, $p_obj_type, $p_category_title, null);
            }
        }
    }
}