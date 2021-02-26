<?php

namespace idoit\Module\Search\Index\Data\Source;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Config
 *
 * @package idoit\Module\Search\Index\Data\Source
 * @codeCoverageIgnore
 */
class Config
{
    /**
     * @var int[]
     */
    private $objectIds = [];

    /**
     * @var int[]
     */
    private $categoryIds = [];

    /**
     * @return int[]
     */
    public function getObjectIds()
    {
        return $this->objectIds;
    }

    /**
     * @param int[] $objectIds
     */
    public function setObjectIds(array $objectIds)
    {
        $this->objectIds = $objectIds;
    }

    /**
     * @return bool
     */
    public function hasObjectIds()
    {
        return !empty($this->getObjectIds());
    }

    /**
     * @return int[]
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return bool
     */
    public function hasCategoryIds()
    {
        return !empty($this->getCategoryIds());
    }
}
