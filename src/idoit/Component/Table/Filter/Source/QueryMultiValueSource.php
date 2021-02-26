<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Source;

/**
 * QueryMultiValueSource
 *
 * Retriever of multiple values from
 * any source with validating capabilities.
 *
 * @package idoit\Component\Table\Filter\Source
 */
class QueryMultiValueSource implements SourceInterface
{
    /**
     * Parameter names
     *
     * @var array
     */
    private $parameterNames = [];

    /**
     * Source
     *
     * @var array
     */
    private $source = null;

    /**
     * Callable for validating values
     *
     * @var callable
     */
    private $validatorCallable = null;

    /**
     * Get parameter names
     *
     * @return array
     */
    public function getParameterNames()
    {
        return $this->parameterNames;
    }

    /**
     * Set parameter names
     *
     * @param array $parameterNames
     *
     * @return QueryMultiValueSource
     */
    public function setParameterNames($parameterNames)
    {
        $this->parameterNames = $parameterNames;

        return $this;
    }

    /**
     * Get source of data retrieval
     *
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set source of data retrieval
     *
     * @param array $source
     *
     * @return QueryMultiValueSource
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get check callable
     *
     * @return callable
     */
    public function getValidatorCallable()
    {
        return $this->validatorCallable;
    }

    /**
     * Set check callable
     *
     * @param callable $validatorCallable
     *
     * @return QueryMultiValueSource
     */
    public function setValidatorCallable(callable $validatorCallable)
    {
        $this->validatorCallable = $validatorCallable;

        return $this;
    }

    /**
     * QueryMultiValueSource constructor.
     *
     * @param array      $parameterNames
     * @param array|null $source
     */
    public function __construct(array $parameterNames, $source = [])
    {
        // Set parameter names
        $this->parameterNames = $parameterNames;

        // Set $_GET as source per default
        $this->setSource($_GET);

        // Reset source if one is provided
        if ($source) {
            $this->setSource($source);
        }
    }

    /**
     * Get values of setted parameter names from
     *
     * @return array
     */
    public function get()
    {
        // Get source
        $source = $this->getSource();
        $queryTarget = [];

        // Iterate over parameters
        foreach ($this->getParameterNames() as $parameterName) {
            // Check for existence and validity by executing provided checkerCallable
            if (isset($source[$parameterName]) && $this->validateParameter($parameterName, $source[$parameterName])) {
                $queryTarget[$parameterName] = $source[$parameterName];
            }
        }

        return $queryTarget;
    }

    /**
     * Validate parameter
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     *
     * @return bool
     */
    private function validateParameter($parameterName, $parameterValue)
    {
        // Get callable validator
        $validatorCallable = $this->getValidatorCallable();

        // Check for provided validator
        if (!empty($validatorCallable) && is_callable($validatorCallable)) {
            // Execute validation
            return $validatorCallable($parameterName, $parameterValue);
        }

        return true;
    }
}
