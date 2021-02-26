<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

/**
 * Class Value
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class Value
{

    /**
     * @var
     */
    protected $value;

    /**
     * @var
     */
    protected $viewValue;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $databaseValue
     *
     * @return Value
     */
    public function setValue($databaseValue)
    {
        $this->value = $databaseValue;

        return $this;
    }

    /**
     * @param $databaseValue
     *
     * @return $this
     */
    public function appendValue($databaseValue)
    {
        $this->value .= ',' . $databaseValue;

        return $this;
    }

    /**
     * @param $viewValue
     *
     * @return $this
     */
    public function appendViewValue($viewValue)
    {
        $this->viewValue .= ',' . $viewValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getViewValue()
    {
        return $this->viewValue;
    }

    /**
     * @param mixed $viewValue
     *
     * @return Value
     */
    public function setViewValue($viewValue)
    {
        $this->viewValue = $viewValue;

        return $this;
    }
}
