<?php

class isys_component_dao_result_static implements isys_component_dao_result_interface
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Count elements of an object
     *
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Returns a row from a DAO result. The result type is specified by $p_result_type and defaults to a assoc+numeric array as result.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     */
    public function get_row($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        $row = current($this->data);

        next($this->data);

        return $row;
    }

    /**
     * Returns the specified key value from the fetched row.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get_row_value($p_key)
    {
        return $this->data[$p_key];
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function __to_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function __as_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        return $this->data;
    }

    /**
     * @return bool|mixed
     */
    public function reset_pointer()
    {
        return reset($this->data);
    }

    /**
     * @return $this
     */
    public function free_result()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Returns the number of rows - A wrapper method for "count()".
     *
     * @deprecated  Use "count()" instead.
     * @return      integer
     */
    public function num_rows()
    {
        return $this->count();
    }

    /**
     * Retrieves the number of fields from a query.
     *
     * @return  integer
     */
    public function num_fields()
    {
        return count(array_keys($this->data));
    }
}
