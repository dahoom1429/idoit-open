<?php
/**
 * i-doit
 *
 * Simple XML Element
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Dennus Stuecken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (class_exists("SimpleXMLElement")) {
    class isys_library_xml extends SimpleXMLElement
    {

        /**
         * Return "simple_xml" parsed string
         *
         * @param string $p_file
         *
         * @return isys_library_xml
         */
        public function simple_xml_parse($p_file)
        {
            return simplexml_load_file($p_file, "isys_library_xml");
        }

        /**
         * Return "simple_xml" parsed string
         *
         * @param string $p_string
         *
         * @return isys_library_xml
         */
        public function simple_xml_string($p_string)
        {
            return simplexml_load_string($p_string, "isys_library_xml");
        }

        /**
         * Get a value by key/node
         *
         * @param string $p_key
         *
         * @return mixed
         */
        public function get($p_key)
        {
            if (isset($this->$p_key)) {
                return $this->$p_key;
            }
        }

        /**
         * Return attribute of current node
         *
         * @param string $p_attribute
         *
         * @return mixed
         */
        public function get_attribute($p_attribute)
        {
            if (is_array($this->attributes()) && count($this->attributes()) > 0) {
                $l_attributes = (array)$this->attributes();
                $l_attributes = $l_attributes["@attributes"];

                if (isset($l_attributes[$p_attribute])) {
                    return (string)$l_attributes[$p_attribute];
                }
            }

            return false;
        }

        /**
         * Get all possible attribute names
         *
         * @return mixed
         */
        public function get_attribute_names()
        {
            $l_attr = [];
            foreach ($this->attributes() as $l_key => $l_value) {
                $l_attr[] = (string)$l_key;
            }

            return (array)$l_attr;
        }

        /**
         * Get all or more than one attributes
         *
         * @param array $p_attributes
         *
         * @return mixed
         */
        public function get_attributes($p_attributes = null)
        {
            $l_attr = [];

            if ($p_attributes == null) {
                foreach ($this->attributes() as $l_key => $l_val) {
                    $l_attr[$l_key] = (string)$l_val;
                }
            } else {
                foreach ($p_attributes as $l_val) {
                    $l_attr[$l_val] = $this->get_attribute($l_val);
                }
            }

            return $l_attr;
        }

        /**
         * Count children of current node
         *
         * @return int
         */
        public function count_children()
        {
            return count((array)$this->children());
        }

        /**
         * Count attributes of current node
         *
         * @return int
         */
        public function count_attributes()
        {
            return count((array)$this->attributes());
        }

        /**
         * Inserts a cdata value to the current node
         *
         * @param $p_value
         */
        public function addCData($p_value)
        {
            if (function_exists('dom_import_simplexml')) {
                $l_node = dom_import_simplexml($this);
                $l_no = $l_node->ownerDocument;
                $l_node->appendChild($l_no->createCDATASection($p_value));
            }
        }

    }
}