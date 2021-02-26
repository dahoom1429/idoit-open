<?php

/**
 * i-doit core classes
 *  route definition
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_route
{

    /**
     * The callback method to execute when the route is matched
     *
     * Any valid "callable" type is allowed
     *
     * @link   http://php.net/manual/en/language.types.callable.php
     * @var callable
     * @access protected
     */
    protected $callback;

    /**
     * The HTTP method to match
     *
     * May either be represented as a string or an array containing multiple methods to match
     *
     * Examples:
     * - 'POST'
     * - array('GET', 'POST')
     *
     * @var string|array
     * @access protected
     */
    protected $method;

    /**
     * The URL path to match
     *
     * Allows for regular expression matching and/or basic string matching
     *
     * Examples:
     * - '/posts'
     * - '/posts/[:post_slug]'
     * - '/posts/[i:id]'
     *
     * @var string
     * @access protected
     */
    protected $path;

    /**
     * Get the callback
     *
     * @access public
     * @return callable
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Set the callback
     *
     * @param callable $p_callback
     *
     * @throws InvalidArgumentException If the callback isn't a callable
     * @access public
     * @return Route
     */
    public function set_callback($p_callback)
    {
        if (!is_callable($p_callback)) {
            throw new InvalidArgumentException('Route Callback is not a valid callable: ' . var_export($p_callback, true));
        }

        $this->callback = $p_callback;

        return $this;
    }

    /**
     * Get the path
     *
     * @access public
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Set the path
     *
     * @param string $path
     *
     * @access public
     * @return Route
     */
    public function set_path($p_path)
    {
        $this->path = (string)$p_path;

        return $this;
    }

    /**
     * Get the method
     *
     * @access public
     * @return string|array
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Set the method
     *
     * @param string|array $method
     *
     * @throws InvalidArgumentException If a non-string or non-array type is passed
     * @access public
     * @return Route
     */
    public function set_method($p_method)
    {
        // Allow null, otherwise expect an array or a string
        if (null !== $p_method && !is_array($p_method) && !is_string($p_method)) {
            throw new InvalidArgumentException('Expected an array or string. Got a ' . gettype($p_method));
        }

        $this->method = $p_method;

        return $this;
    }

    /**
     * Constructor
     *
     * @param callable     $p_callback
     * @param string       $p_path
     * @param string|array $p_method
     *
     * @access public
     */
    public function __construct($p_callback, $p_path, $p_method = 'GET')
    {
        // Initialize route
        $this->set_callback($p_callback);
        $this->set_method($p_method);

        $this->path = $p_path;
    }
}