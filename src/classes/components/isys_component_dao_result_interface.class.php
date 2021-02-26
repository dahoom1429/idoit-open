<?php

define("IDOIT_C__DAO_RESULT_TYPE_ARRAY", 1);
define("IDOIT_C__DAO_RESULT_TYPE_ROW", 2);
define("IDOIT_C__DAO_RESULT_TYPE_ALL", 3);

interface isys_component_dao_result_interface extends Countable
{
    /**
     * Returns a row from a DAO result. The result type is specified by $p_result_type and defaults to a assoc+numeric array as result.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     */
    public function get_row($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY);

    /**
     * Returns the specified key value from the fetched row.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get_row_value($p_key);

    /**
     * Converts current dao result into a single array.
     *
     * @param integer $p_result_type
     *
     * @return array
     * @deprecated Please refer to methods that don't start with double underscore.
     */
    public function __to_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY);

    /**
     * Converts current dao result into a multidimensional array.
     *
     * @param integer $p_result_type
     *
     * @return array
     * @deprecated Please refer to methods that don't start with double underscore.
     */
    public function __as_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY);

    /**
     * @return bool|mixed
     */
    public function reset_pointer();

    /**
     * @return $this
     */
    public function free_result();

    /**
     * Returns the number of rows - A wrapper method for "count()".
     *
     * @deprecated  Use "count()" instead.
     * @return      integer
     */
    public function num_rows();

    /**
     * Retrieves the number of fields from a query.
     *
     * @return  integer
     */
    public function num_fields();
}
