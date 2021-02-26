<?php

namespace idoit\Module\Search\Index;

/**
 * i-doit
 *
 * Document
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @codeCoverageIgnore
 */
class Document
{
    /**
     * @var integer
     */
    private $version;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $key;

    /**
     * @var DocumentMetadata
     */
    private $metadata;

    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $reference;

    /**
     * Document constructor.
     *
     * @param DocumentMetadata $metadata
     */
    public function __construct(DocumentMetadata $metadata) {
        $this->metadata = $metadata;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param int $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return DocumentMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
