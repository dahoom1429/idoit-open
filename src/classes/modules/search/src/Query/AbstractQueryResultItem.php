<?php

namespace idoit\Module\Search\Query;

use idoit\Module\Search\Index\DocumentMetadata;

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
abstract class AbstractQueryResultItem implements \JsonSerializable
{
    /**
     * @var Condition[]
     */
    protected $conditions = [];

    /**
     * @var int
     */
    protected $documentId;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var double
     */
    protected $score;

    /**
     * @var string
     */
    protected $type = 'search';

    /**
     * @var string
     */
    protected $value;

    /**
     * @var DocumentMetadata
     */
    private $documentMetadata;

    /**
     * @inheritdoc
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return int
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return DocumentMetadata
     */
    public function getDocumentMetadata()
    {
        return $this->documentMetadata;
    }

    /**
     * @return string
     */
    abstract public function getStatus();

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
            'score'      => $this->getScore(),
            'status'      => $this->getStatus()
        ];
    }

    /**
     * QueryResultItem constructor.
     *
     * @param DocumentMetadata $documentMetadata
     * @param int              $documentId
     * @param string           $key
     * @param string           $value
     * @param double           $score
     * @param array            $conditions
     */
    public function __construct(DocumentMetadata $documentMetadata, $documentId, $key, $value, $score, array $conditions)
    {
        $this->documentMetadata = $documentMetadata;
        $this->documentId = $documentId;
        $this->key = $key;
        $this->value = $value;
        $this->score = $score;
        $this->conditions = $conditions;
    }

}
