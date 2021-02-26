<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;

class SearchCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__UNIVERSAL__SEARCH';
    }

    /**
     * This method will not return anything, because the search works with no parameter (except the search string, of course).
     *
     * @return array
     */
    public function retrieveOverview()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];
        $result = $this->dao->search_objects(urldecode($this->parameter), null, null, $this->getFilterQueryConditions());

        while ($row = $result->get_row()) {
            $return[] = $row['isys_obj__id'];
        }

        return $return;
    }
}
