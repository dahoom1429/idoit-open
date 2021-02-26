<?php

namespace idoit\Module\Search\Query;

use idoit\Module\Search\Query\Protocol\QueryResultItem as QueryResultItemProtocol;

/**
 * i-doit
 *
 * Default query result item
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class QueryResultItem extends AbstractQueryResultItem implements QueryResultItemProtocol, \JsonSerializable
{

    /**
     * @return string
     */
    public function getLink()
    {
        return rtrim(\isys_application::instance()->www_path, '/') . '/' . $this->getType() . '/' . $this->getDocumentId();
    }

    /**
     * JsonSerializable Interface
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'documentId' => $this->getDocumentId(),
            'key'        => $this->getKey(),
            'value'      => $this->getValue(),
            'type'       => $this->getType(),
            'link'       => $this->getLink(),
            'score'      => $this->getScore()
        ];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return '';
    }
}
