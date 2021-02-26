<?php

namespace idoit\Component\Table\Pagerfanta\Adapter;

/**
 * i-doit ListDaoAdapter for Pagerfanta.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ListDaoAdapter extends DaoAdapter
{
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
        return $this->dao->load($offset, $length);
    }
}