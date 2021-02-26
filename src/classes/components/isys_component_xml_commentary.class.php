<?php

/**
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_xml_commentary extends isys_component_xml_object
{
    /**
     * @var  string
     */
    private $strCommentary = null;

    /**
     * Output commentary.
     *
     * @return  string
     */
    function get_object()
    {
        return "<!--" . $this->strCommentary . "-->";
    }

    /**
     * Constructor.
     *
     * @param  string $p_strCommentary
     */
    function __construct($p_strCommentary)
    {
        $this->strCommentary = $p_strCommentary;
    }
}