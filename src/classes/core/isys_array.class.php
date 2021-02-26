<?php

/**
 * i-doit Array Handler.
 *
 * @credits     Credits go out to http://kohanaframework.org/ for some nice function ideas
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_array extends ArrayObject
{
    /**
     * Iterator Instance
     *
     * @var  ArrayIterator
     */
    private $m_iterator = null;

    /**
     * Retrieve a single key from an array. If the key does not exist in the array, the default value will be returned instead.
     *
     * @param   string $l_key
     * @param   mixed  $p_default
     *
     * @return  mixed
     */
    public function get($l_key, $p_default = null)
    {
        return isset($this[$l_key]) ? $this[$l_key] : $p_default;
    }

    /**
     * Checks if the given key exists in this instance.
     *
     * @param   string $p_key
     *
     * @return  boolean
     */
    public function has($p_key)
    {
        return isset($this[$p_key]);
    }

    /**
     * Gets a value from an array using a dot separated path.
     *
     * @param   string $p_path
     * @param   mixed  $p_default
     *
     * @return  isys_array
     */
    public function path($p_path, $p_default = null)
    {
        if (array_key_exists($p_path, $this)) {
            // No need to do extra processing
            return $this[$p_path];
        }

        // Remove starting delimiters and spaces
        $p_path = ltrim($p_path, ". ");

        // Remove ending delimiters, spaces, and wildcards
        $p_path = rtrim($p_path, ". *");

        // Split the keys by delimiter
        $l_keys = explode('.', $p_path);

        // Get a reference to $this.
        $l_array = &$this;

        do {
            $l_key = array_shift($l_keys);

            if (ctype_digit($l_key)) {
                // Make the key an integer
                $l_key = (int)$l_key;
            }

            if (isset($l_array[$l_key])) {
                if ($l_keys) {
                    if (is_array($l_array[$l_key]) || $l_array[$l_key] instanceof isys_array) {
                        // Dig down into the next part of the path.
                        $l_array = $l_array[$l_key];
                    } else {
                        // Unable to dig deeper!
                        break;
                    }
                } else {
                    // Found the path requested.
                    return $l_array[$l_key];
                }
            } elseif ($l_key === '*') {
                // Handle wildcards.
                $l_values = new self();

                foreach ($l_array as $l_arr) {
                    if (method_exists($l_arr, 'path')) {
                        if ($l_value = $l_arr->path(implode('.', $l_keys))) {
                            $l_values[] = $l_value;
                        }
                    }
                }

                if ($l_values) {
                    // Found the values requested
                    return $l_values;
                } else {
                    // Unable to dig deeper
                    break;
                }
            } else if ($l_key[0] === '@') // Path component selector.
            {
                $l_selector = substr($l_key, 1);
                switch ($l_selector) {
                    case 'last':
                        $l_array = end($l_array);
                        //return $l_array->offsetGet($l_array->count()-1);
                        break;
                    case 'primary':
                        //$l_array = end($l_array);
                        $l_array = $l_array->offsetGet($l_array->primaryIndex());
                        break;
                    case 'first':
                        $l_array = reset($l_array);
                        //return $l_array->offsetGet(0);
                        break;
                }

                if (count($l_keys) === 0) {
                    return $l_array;
                }
            } else {
                // Unable to dig deeper
                break;
            }
        } while ($l_keys);

        if ($p_default === null) {
            $p_default = new isys_array();
        }

        // Unable to find the value requested
        return $p_default;
    }

    /**
     * Returns the primary index of this dataset.
     *
     * @return  mixed
     */
    public function primaryIndex()
    {
        return key($this);
    }

    /**
     * Set a value on an array by path.
     *
     * @param  string $p_path
     * @param  mixed  $p_value
     */
    public function set_path($p_path, $p_value)
    {
        // The path has already been separated into keys.
        $l_keys = $p_path;
        if (!is_array($p_path)) {
            // Split the keys by delimiter.
            $l_keys = explode('.', $p_path);
        }

        // Set current $l_array to inner-most array path.
        while (count($l_keys) > 1) {
            $l_key = array_shift($l_keys);

            if (ctype_digit($l_key)) {
                // Make the key an integer.
                $l_key = (int)$l_key;
            }

            if (!isset($this[$l_key])) {
                $this[$l_key] = [];
            }

            $l_array = &$this[$l_key];
        }

        // Set key on inner-most array.
        $l_array[array_shift($l_keys)] = $p_value;
    }

    /**
     * Retrieves muliple single-key values. Like Prototype's Enumerable.pluck().
     *
     * @param   string $l_key
     *
     * @return  isys_array
     */
    public function pluck($l_key)
    {
        $l_values = new isys_array();

        foreach ($this as $l_row) {
            if (isset($l_row[$l_key])) {
                $l_values[] = $l_row[$l_key];
            }
        }

        $this->getIterator()
            ->rewind();

        return $l_values;
    }

    /**
     * Join current isys_array elements with a string.
     *
     * @param   string $p_glue
     *
     * @return  string
     */
    public function implode($p_glue = ', ')
    {
        return implode($p_glue, $this->toArray());
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     *
     * @return  array
     */
    public function flatten()
    {
        $is_assoc = isset($this[0]) ? false : true;

        $flat = [];
        foreach ($this as $l_key => $l_value) {
            if ($l_value instanceof isys_array) {
                $flat = array_merge($flat, $l_value->flatten());
            } else if (is_array($l_value)) {
                $l_value = new isys_array($l_value);
                $flat = array_merge($flat, $l_value->flatten());
            } else {
                if ($is_assoc) {
                    $flat[$l_key] = $l_value;
                } else {
                    $flat[] = $l_value;
                }
            }
        }

        $this->getIterator()
            ->rewind();

        return $flat;
    }

    /**
     * Cast current object to array.
     *
     * @return  array
     */
    public function toArray()
    {
        return (array)$this;
    }

    /**
     * "To string" method.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __toString()
    {
        return $this->implode();
    }

    /**
     * Returns the iterator.
     *
     * @return  ArrayIterator
     */
    public function getIterator()
    {
        if (!$this->m_iterator) {
            $this->m_iterator = parent::getIterator();
            $this->m_iterator->rewind();
        }

        return $this->m_iterator;
    }

    /**
     * Iterator next().
     *
     * @return  mixed
     */
    public function next()
    {
        $this->getIterator()
            ->next();

        return $this->current();
    }

    /**
     * Iterator current().
     *
     * @return  mixed
     */
    public function current()
    {
        return $this->getIterator()
            ->current();
    }

    /**
     * Iterator key().
     *
     * @return  mixed
     */
    public function key()
    {
        return $this->getIterator()
            ->key();
    }

    /**
     * Iterator rewind().
     *
     * @return  $this
     */
    public function rewind()
    {
        $this->getIterator()
            ->rewind();

        return $this;
    }

    /**
     * Iterator valid().
     *
     * @return  boolean
     */
    public function valid()
    {
        return $this->getIterator()
            ->valid();
    }

    /**
     * Fallback "data()" method for isys_cmdb_dao_category_data.
     *
     * @param   string $p_category_const
     *
     * @return  $this
     */
    public function data($p_category_const = null)
    {
        return $this;
    }
}