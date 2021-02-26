<?php

namespace idoit\Component\Table\Pagerfanta\Adapter;

use idoit\Exception\Exception;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * i-doit DaoAdapter for Pagerfanta.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DaoAdapter implements AdapterInterface
{
    /**
     * @var \isys_cmdb_dao_list_objects
     */
    protected $dao;

    /**
     * Cache for "$this->dao->get_object_count()".
     *
     * @var integer
     */
    protected $nbResults = null;

    /**
     * DaoAdapter constructor.
     *
     * @param \isys_cmdb_dao_list_objects $dao
     */
    public function __construct(\isys_cmdb_dao_list_objects $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if ($this->nbResults === null) {
            return ($this->nbResults = $this->dao->get_object_count());
        }

        return $this->nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $l_query = $this->dao->get_table_query($offset, $length);

        return $this->dao->retrieve($l_query)
            ->__as_array();
    }
}