<?php

namespace idoit\Module\Search\Query\Engine\Mysql;

use idoit\Model\Dao\Base;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Engine\AbstractQuery;
use idoit\Module\Search\Query\Protocol\Query as QueryProtocol;
use idoit\Module\Search\Query\QueryResult;
use isys_component_database as Database;
use isys_tenantsettings as TenantSettings;

/**
 * i-doit
 *
 * MySQL Search Query
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Query extends AbstractQuery implements QueryProtocol
{

    /**
     * @var Base
     */
    private $dao;

    /**
     * Boolean Syntax
     * Result of "show global variables like 'ft_boolean_syntax';"
     *
     * @var string
     */
    private $ftsTerm = '+ -><()~*:"&|@';

    /**
     * SQL Statement
     *
     * @var string
     */
    private $statement = '';

    /**
     * Sanitize Keyword: Prevent SQL Errors, strip boolean syntax from keywords.
     *
     * @param string $keyword
     *
     * @return string
     */
    private function sanitizeForBoolMatching($keyword = '')
    {
        if ($keyword && preg_match('/[' . $this->ftsTerm . ']/', $keyword)) {
            // Fixing error "syntax error, unexpected $end, expecting FTS_TERM or FTS_NUMB or '*'" with str_replace and rtrim
            // by trimming and replacing ftsTerms
            $keyword = trim(ltrim(rtrim(str_replace([
                '-*',
                '**'
            ], '*', $keyword), $this->ftsTerm), $this->ftsTerm));

            // Replace all remaining condition keywords by a space
            // This fixes "syntax error, unexpected '-'" and "syntax error, unexpected $end, expecting FTS_TERM or FTS_NUMB or '*'" by replacing all possible FTS terms
            // see ID-3453
            $keyword = str_replace(['(', ')', '>', '<', '~', '@'], ' ', $keyword);
        }

        return $keyword;
    }

    /**
     * Prepare like condition
     *
     * @param      $keyword
     * @param bool $negation
     *
     * @return string
     */
    private function like($keyword, $negation = false)
    {
        return sprintf('(isys_search_idx__value %s \'%s\')', $negation ? 'NOT LIKE' : 'LIKE', '%' . $keyword . '%');
    }

    /**
     * Query Database and search for given conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions)
    {
        $matchers = $matchers2 = [];
        $result = new QueryResult($conditions);
        $likeMatch = [];

        $keywordsForScore = [];

        foreach ($conditions as $condition) {
            $conditionKeyword = trim($condition->getKeyword());

            // Sanitize and split Keywords by space into independent strings
            $keywordSplit = explode(' ', $this->sanitizeForBoolMatching($conditionKeyword));

            foreach ($keywordSplit as $keyword) {
                /**
                 * Forcing keyword to be over one character
                 *
                 * @see ID-2984
                 */
                if (strlen(trim($keyword)) <= 1) {
                    continue;
                }
                $keywordsForScore[] = $keyword;

                if ($condition->getMode() === Condition::MODE_DEEP) {
                    // Prepare like condition for each keyword if search is operated in deep search mode
                    $likeMatch[] = $this->like($keyword, $condition->isNegation());
                } else {
                    // ID-3876: Replace non-word-characters with spaces according to: http://stackoverflow.com/a/26537463
                    $keyword = preg_replace('/[^\p{L}\p{N}_\.]+/u', ' ', $keyword);
                    //                                     /\
                    // not replacing the dot anymore since this does not allow to search for
                    // e.g. synetics.de (would result in "synetics de") and "de" will return unwanted results

                    if (strlen(trim($keyword)) <= 1) {
                        continue;
                    }

                    // Add keyword rule to the matchers array
                    if ($condition->isNegation() || $keyword[0] === '-') {
                        // Negate by prepending a minus
                        $matchers[] = '-' . $keyword . '*';
                    } else {
                        $matchers[] = $keyword . '*';
                    }

                    // Add another > matching rule, as suggested by Percona.
                    $matchers2[] = ' >"' . $keyword . '"';
                }
            }
        }

        $matching = '';

        // Prepare boolean fulltext matching
        if (count($matchers)) {
            $matching = '(MATCH(isys_search_idx__value) AGAINST (' . $this->dao->convert_sql_text(implode(' ', $matchers) . implode(' ', $matchers2)) . ' IN BOOLEAN MODE))';
        }

        if ($matching) {
            $matching = 'AND ' . $matching;
        }

        // Simple priority calculation which is only relevant for the api
        $scoreSelection = [];
        $fullSearchString = implode(' ', $keywordsForScore);
        $scoreSelectionString = '(CASE WHEN (LENGTH(isys_search_idx__value) = LENGTH(' . $this->dao->convert_sql_text($fullSearchString) . ')) 
            AND isys_search_idx__value = ' . $this->dao->convert_sql_text($fullSearchString) . ' THEN 100 ELSE
            100 - %s END)';

        $replaceTemplate = 'REPLACE(%s, %s, %s)';
        $placeholder = '';
        foreach ($keywordsForScore as $key => $keyword) {
            if (isset($keywordsForScore[$key+1])) {
                $replaceTemplate = sprintf($replaceTemplate, $replaceTemplate, $this->dao->convert_sql_text($keyword), '\'\'');
            } else {
                $placeholder = sprintf($replaceTemplate, 'isys_search_idx__value', $this->dao->convert_sql_text($keyword), '\'\'');
            }
        }

        $scoreSelectionString = sprintf($scoreSelectionString, 'LENGTH(' . $placeholder . ')');

        /** Additionally matching over the search result via HAVING LIKE, since there are problems with ftsSearchTerm separated keywords.. */
        /** @see https://i-doit.atlassian.net/wiki/pages/viewpage.action?pageId=30441489 */
        $likeMatchCondition = count($likeMatch) ? 'AND ' . implode(' AND ', $likeMatch) : '';
        $query = sprintf($this->statement, $scoreSelectionString, $matching, $likeMatchCondition, TenantSettings::get('search.limit', '2500'));

        $daoResult = $this->dao->retrieve($query);

        while ($row = $daoResult->get_row()) {
            foreach ($result->getConditions() as $condition) {
                if (!mb_stristr($row['searchValue'], $condition->getKeyword())) {
                    continue 2;
                }
            }
            
            $result->addItem($this->getQueryItemInstance(
                $row['type'],
                DocumentMetadata::createInstanceFromArray(json_decode($row['metadata'], true)),
                $row['searchReference'],
                \isys_application::instance()->container->get('language')->get_in_text($row['searchKey']),
                $row['searchValue'],
                $row['priority'],
                $conditions
            ));
        }

        return $result;
    }

    /**
     * Mysql constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $authObjects = new \isys_auth_cmdb_objects();

        $this->dao = new Base($database);
        $this->statement = '
            SELECT isys_search_idx__type AS type, isys_search_idx__metadata as metadata, isys_search_idx__key AS searchKey, (
                CASE isys_obj__title WHEN isys_search_idx__value THEN isys_search_idx__value ELSE CONCAT(isys_obj__title, ": ", isys_search_idx__value) END
            ) AS searchValue, isys_search_idx__reference AS searchReference,
            %s AS priority
            FROM isys_search_idx
            LEFT JOIN isys_obj ON isys_obj__id = isys_search_idx__reference
            WHERE TRUE %s %s ' . $authObjects->get_allowed_objects_condition() . ' ORDER BY priority DESC LIMIT %s;';
    }
}
