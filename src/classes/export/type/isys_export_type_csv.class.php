<?php

/**
 * @deprecated  Use "\League\Csv\Writer" for writing CSV files!
 * @package     i-doit
 * @subpackage  Export
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_export_type_csv extends isys_export_type
{
    /**
     * @var  string
     */
    protected $m_extension = 'csv';

    /**
     * @var  string
     */
    private $m_max_line = '';

    /**
     * Parses an array and returns a copy of $this.
     *
     * @param   array  $p_array
     * @param   string $p_export_format
     *
     * @throws  isys_exception_general
     * @return  string
     */
    public function parse($p_array, $p_export_format = null)
    {
        if (is_array($p_array)) {
            $l_string = '';
            $this->set_max_line($p_array);

            foreach ($p_array as $l_column) {
                for ($l_counter = 0; $l_counter <= $this->m_max_line; $l_counter++) {
                    $l_string .= $l_column[$l_counter] . ';';
                }

                $l_string .= PHP_EOL;
            }

            $this->set_formatted_export($l_string);

            return $this;
        }

        throw new isys_exception_general('Input not an array. (isys_export_type_csv->parse())');
    }

    /**
     * Sets max columns per line
     *
     * @param array $p_array
     */
    private function set_max_line($p_array)
    {
        if (!is_countable($p_array)) {
            return;
        }
        for ($l_i = 0; $l_i < 5; $l_i++) {
            if (!is_countable($p_array[$l_i])) {
                continue;
            }

            if ($this->m_max_line < count($p_array[$l_i])) {
                $this->m_max_line = count($p_array[$l_i]);
            }
        }
    }

    /**
     * Constructor.
     *
     * @param  string $p_encoding
     */
    public function __construct($p_encoding = null)
    {
        if ($p_encoding !== null) {
            $this->m_encoding = $p_encoding;
        }
    }
}
