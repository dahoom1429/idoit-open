<?php

/**
 * Basis class for xml objects.
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_xml_object
{
    /**
     * Each class which extends isys_component_xml_object has to override this methode.
     *
     * @return  string
     */
    function get_object()
    {
        return '';
    }
}