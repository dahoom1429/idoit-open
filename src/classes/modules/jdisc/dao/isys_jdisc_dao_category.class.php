<?php

/**
 * Used for adding new category imports
 *
 * Class isys_jdisc_dao_category
 */
class isys_jdisc_dao_category extends isys_jdisc_dao_data
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var bool
     */
    protected $isSelectable = true;

    /**
     * @return bool
     */
    public function isSelectable(): bool
    {
        return $this->isSelectable;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }
}
