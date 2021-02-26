<?php
/**
 * @package    i-doit
 * @subpackage Components_XML
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * elements are atomic xml object which can be parts of nodes
 * elements are represented by their names
 * elements may have one value
 * elements may have multiple atrributes
 * example: <element id="1">value</element>
 */
class isys_component_xml_element extends isys_component_xml_object
{
    private $arrAttribute;

    private $strName;

    private $strValue;

    /**
     * Store attribute datapairs to the start tag of an element.
     *
     * @param array $p_arrPara
     */
    public function setAttribute($p_arrPara)
    {
        $this->arrAttribute = $p_arrPara;
    }

    /**
     * Output the element data.
     *
     * @return string
     */
    public function get_object()
    {
        $l_strAttr = '';

        if ($this->strValue === null || trim($this->strValue) === '') {
            $this->strValue = 'NULL';
        }

        if (is_array($this->arrAttribute)) {
            foreach ($this->arrAttribute as $key => $value) {
                $l_strAttr .= ' ' . $key . '="' . $value . '" ';
            }
        }

        return '<' . $this->strName . $l_strAttr . '>' . $this->strValue . '</' . $this->strName . '>';
    }

    /**
     * isys_component_xml_element constructor.
     *
     * @param string $p_strName
     * @param mixed  $p_value
     * @param array  $p_arrAttribute
     */
    public function __construct($p_strName, $p_value, $p_arrAttribute = null)
    {
        $this->strName = $p_strName;
        $this->strValue = $p_value;

        if ($p_arrAttribute !== null) {
            $this->setAttribute($p_arrAttribute);
        }
    }
}
