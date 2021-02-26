<?php

namespace idoit\Module\Cmdb\Model\Matcher\Ci;

use idoit\Module\Cmdb\Model\Matcher\AbstractMatcher;
use idoit\Module\Cmdb\Model\Matcher\Match;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;
use idoit\Module\Cmdb\Model\Matcher\Protocol\Matchable;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiMatcher extends AbstractMatcher implements Matchable
{
    /**
     * @var CiIdentifiers
     */
    protected $identifiers;

    /**
     * @param MatchKeyword[] $matchKeywords
     *
     * @return Match
     */
    public function match(array $matchKeywords)
    {
        // Start matching process
        $unionSelect = '';
        $matchCount = 0;
        $objectid = null;
        $title = null;
        $minMatch = $this->config->getMinMatch();
        $matchResult = [];

        foreach ($matchKeywords as $keyword) {
            if ($this->identifiers->hasIdentifier($keyword->getKey())) {
                $identifier = $this->identifiers->getIdentifier($keyword->getKey());
                $value = $keyword->getValue();
                $condition = $keyword->getCondition();

                // Build UNION SELECT
                if ($value) {
                    $unionSelect .= $identifier->getSqlSelect($value, $condition) . ' UNION ';
                }
            }
        }

        if (strlen($unionSelect) > 0 && trim($unionSelect) !== '') {
            $unionSelect = 'SELECT *, count(matchblock.id) AS matchings FROM (' . rtrim($unionSelect, ' UNION ') .
                ') AS matchblock GROUP BY id HAVING matchings > 0 ORDER BY matchings DESC LIMIT 3';

            // Execute UNION SELECT
            $resultSet = $this->config->getDao()
                ->retrieve($unionSelect);
            $matchCount = $resultSet->count();
            while ($row = $resultSet->get_row()) {
                // The first one is always with the highest matchcount
                if (!$objectid && $row['matchings'] >= $minMatch) {
                    $objectid = $row['id'];
                    $title = $row['title'];
                }
                $matchResult[] = Match::factory()
                    ->setId($row['id'])
                    ->setTitle($row['title']);
            }
        }

        return Match::factory()
            ->setId($objectid)
            ->setTitle($title)
            ->setMatchResult($matchResult)
            ->setMatchCount($matchCount);
    }

    /**
     * CiMatcher constructor.
     *
     * @param MatchConfig $config
     */
    public function __construct(MatchConfig $config)
    {
        parent::__construct($config);

        $this->identifiers = new CiIdentifiers($config->getBits());
    }

}