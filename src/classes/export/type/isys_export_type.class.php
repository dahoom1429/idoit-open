<?php

/**
 * @deprecated This should not be used!
 * @package    i-doit
 * @subpackage Export
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_export_type
{

    /**
     * Export encoding
     *
     * @var string
     */
    protected $m_encoding = 'utf-8';

    /**
     * Holds the export in array structure
     *
     * @var array
     */
    protected $m_export = [];

    /**
     * Export in Text Format (XML/CSV/..)
     *
     * @var string
     */
    protected $m_export_formatted = '';

    /**
     * File extension
     *
     * @var string
     */
    protected $m_extension = 'txt';

    abstract public function parse($p_array);

    /**
     * Returns the file extension
     */
    public function get_extension()
    {
        return $this->m_extension;
    }

    /**
     * Returns the Export in Text Format
     *
     * @return string
     */
    public function get_export()
    {
        return $this->m_export_formatted;
    }

    /**
     * Returns the unformatted export (array)
     *
     * @return array
     */
    public function get_unformatted_export()
    {
        return $this->m_export;
    }

    /**
     * Set formatted export
     *
     * @param string $p_string
     */
    protected function set_formatted_export($p_string)
    {
        $this->m_export_formatted = $p_string;
    }
}
