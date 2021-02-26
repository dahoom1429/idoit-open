<?php

/**
 * Class for xml node nodes are containers which may contain:
 * a.) other nodes
 * b.) elements like <key id="4">value</key>
 * c.) commentaies like <!-- this is a commentary -->
 *
 * use the method addXmlObj to add one xml object of the list above to a given node
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_xml_node extends isys_component_xml_object
{
    private $arrAttribute;

    private $arrObjValue;

    private $intCountValue;

    private $strName;

    /**
     * Name a node.
     *
     * @param  string $p_strPara
     */
    function setName($p_strPara)
    {
        $this->strName = $p_strPara;
    }

    /**
     * Add other xml_object to the given node.
     *
     * @param  mixed $p_objPara
     */
    function addXmlObj($p_objPara)
    {
        $this->arrObjValue[$this->intCountValue] = $p_objPara;
        $this->intCountValue += 1;
    }

    /**
     * Store attribute datapairs to a node.
     *
     * @param  array $p_arrPara
     */
    function setAttribute($p_arrPara)
    {
        $this->arrAttribute = $p_arrPara;
    }

    /**
     * Output of node data.
     *
     * @return  string
     */
    function get_object()
    {
        $l_value = "";

        for ($l_i = 0;$l_i < $this->intCountValue;$l_i++) {
            $l_strAttr = ""; // string for attribute
            foreach ($this->arrAttribute as $key => $value) {
                $l_strAttr .= " " . $key . "=\"" . $value . "\" ";
            }

            $l_value .= $this->arrObjValue[$l_i]->get_object();
        }

        return "<" . $this->strName . $l_strAttr . ">" . $l_value . "</" . $this->strName . ">";
    }

    /**
     * Constructor.
     *
     * @param  string $p_strName
     */
    function __construct($p_strName = null)
    {
        if ($p_strName != null) {
            $this->setName($p_strName);
        }

        // a counter for each added xml object
        $this->intCountValue = 0;
        $this->strAttribute = [];
    }
}