<?php

namespace idoit\Module\Cmdb\Model\Matcher\Ci;

use idoit\Module\Cmdb\Model\Matcher\AbstractMatcher;
use idoit\Module\Cmdb\Model\Matcher\Data;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;
use idoit\Module\Cmdb\Model\Matcher\Protocol\Retrievable;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiDataRetriever extends AbstractMatcher implements Retrievable
{
    /**
     * @var CiIdentifiers
     */
    protected $identifiers;

    /**
     * Method which retrieves all necessary i-doit data which can be used to match data from i-doit to other source
     *
     * @param int            $objID
     * @param MatchKeyword[] $matchKeywords
     *
     * @return Data
     */
    public function dataRetrieve($objID, array $matchKeywords)
    {
        $select = 'SELECT ';
        $result = [];

        foreach ($matchKeywords as $keyword) {
            if ($this->identifiers->hasIdentifier($keyword->getKey())) {
                $identifier = $this->identifiers->getIdentifier($keyword->getKey());

                // Build Select statement
                $select .= ' (' . $identifier->getDataSqlSelect($objID) . ') AS \'' . $identifier::KEY . '\',';
            }
        }

        if (strlen($select) > 7 && trim($select) !== 'SELECT') {
            $select = rtrim($select, ',');

            // Execute query and return data
            $result = $this->config->getDao()
                ->retrieve($select)
                ->get_row();
        }

        return Data::factory()
            ->setDataResult($result);
    }

    /**
     * CiDataRetriever constructor.
     *
     * @param MatchConfig $config
     */
    public function __construct(MatchConfig $config)
    {
        parent::__construct($config);

        $this->identifiers = new CiIdentifiers($config->getBits());
    }
}
