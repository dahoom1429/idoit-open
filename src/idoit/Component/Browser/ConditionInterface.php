<?php

namespace idoit\Component\Browser;

interface ConditionInterface
{
    /**
     * @param mixed $parameter
     *
     * @return $this
     */
    public function setParameter($parameter);

    /**
     * The "context object ID" can be used by some specific conditions.
     *
     * @param integer $contextObjectId
     *
     * @return $this
     */
    public function setContextObjectId($contextObjectId);

    /**
     * @param bool $displayCount
     *
     * @return $this
     */
    public function enableObjectCount($displayCount);

    /**
     * @return string
     */
    public function getName();

    /**
     * Method for retrieving the object overview (available parameters).
     *
     * @return array
     */
    public function retrieveOverview();

    /**
     * Method for retrieving the objects.
     *
     * @return array
     */
    public function retrieveObjects();

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function registerFilter(FilterInterface $filter);

    /**
     * Convenience method for registering multiple filters at once.
     *
     * @param array $filterData
     *
     * @return $this
     */
    public function registerFilterByArray(array $filterData);

    /**
     * Method to iterate over all filter visitors, passing the objects from one visitor to the next and thereby reducing the amount of resulting objects.
     *
     * @param array $objects
     *
     * @return array
     */
    public function processVisitors(array $objects);

    /**
     * Method that defines if the object order should be retained (necessary for "date" condition).
     *
     * @return boolean
     */
    public function retainObjectOrder();
}
