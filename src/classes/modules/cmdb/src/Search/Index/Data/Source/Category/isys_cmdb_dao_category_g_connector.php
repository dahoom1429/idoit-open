<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Search\Index\Data\Source\Config;

class isys_cmdb_dao_category_g_connector extends AbstractCategorySource
{
    /**
     * Retrieve data for index creation
     *
     * @param Config $config
     *
     * @return array
     */
    public function retrieveData(Config $config)
    {
        return [];
    }
}
