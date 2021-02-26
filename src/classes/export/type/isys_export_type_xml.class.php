<?php
/**
 * @deprecated This should not be used!
 * @package    i-doit
 * @subpackage Export
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Define export content type
 */
define('C__EXPORT__CONTENT_TYPE', 'text/xml');

/**
 * Class isys_export_type_xml
 */
class isys_export_type_xml extends isys_export_type
{

    /**
     * @var string
     */
    protected $m_extension = 'xml';

    /**
     * @var array
     */
    private $m_special_leveled = [
        'implementer',
        'initiator',
        'last_revisor',
        'next_revisor',
        'escalation_contacts'
    ];

    /**
     * @var
     */
    private $m_stylesheet;

    /**
     * Sets an XSL stylesheet for the XML output
     *
     * @param string $p_file
     */
    public function set_stylesheet($p_file)
    {
        $this->m_stylesheet = "<?xml-stylesheet type=\"text/xsl\" href=\"" . $p_file . "\"?>\n\n";
    }

    /**
     * @param $p_array
     *
     * @return bool
     * @throws isys_exception_general
     */
    public function parse_as_xml($p_array)
    {
        $l_parsed = $this->parse($p_array);

        if (is_object($l_parsed) && method_exists($l_parsed, "get_export")) {
            return $l_parsed->get_export();
        }

        return false;
    }

    /**
     * Iterates through $p_data and creates an xml node, where the array key title is
     * used for the node value and every other element is added as an attribute.
     *
     * @param array $p_data
     *
     * @return string
     */
    public function iterate_dialog($p_tag, $p_data, $p_tabs = "\t\t\t\t\t\t", $p_title = null)
    {
        $l_xml = "";
        $l_title = $p_data["title"];
        if (isset($p_data['title'])) {
            unset($p_data["title"]);

            $l_xml .= $p_tabs;
            $l_xml .= "<" . $p_tag;

            foreach ($p_data as $l_dkey => $l_dval) {
                if (is_array($l_dval)) {
                    $l_dval = (isset($l_dval["const"])) ? $l_dval["const"] : $l_dval["title"];
                } elseif (is_object($l_dval) && method_exists($l_dval, "get_data")) {
                    $l_data = $l_dval->get_data();
                    $l_dval = @$l_data[0]["id"];

                    $l_sub_tag = $this->iterate_sub_dialog($l_dkey, $l_data);
                }

                if (!is_null($l_dval) && $l_dval != "") {
                    if (!is_numeric($l_dkey)) {
                        $l_xml .= " " . $l_dkey . "=\"" . htmlspecialchars($l_dval) . "\"";
                    } else {
                        $l_xml .= " key-" . $l_dkey . "=\"" . htmlspecialchars($l_dval) . "\"";
                    }
                }
            }

            if ($l_sub_tag) {
                $l_sub_string = $l_sub_tag . $p_tabs;
                if (trim($l_sub_tag . $p_tabs) == '') {
                    $l_sub_string = htmlspecialchars($l_title);
                }
                $l_xml .= ' title="' . htmlspecialchars($l_title) . '">';
                $l_xml .= $l_sub_string;
            } else {
                if (strpos($p_tag, 'sub_') !== false && $p_tag != 'description') {
                    $p_title = $l_title;
                }

                if (isset($p_title)) {
                    $l_xml .= ' title="' . htmlspecialchars($p_title) . '"';
                }

                $l_xml .= ">";
                if (strstr($l_title, "CDATA")) {
                    $l_xml .= $l_title;
                } else {
                    $l_xml .= htmlspecialchars($l_title);
                }
            }
            $l_xml .= "</" . $p_tag . ">\n";
        } else {
            $l_xml .= $p_tabs . "<" . $p_tag;
            $l_xml .= ' title="' . htmlspecialchars($p_title) . '" ';

            if (is_array($p_data)) {
                foreach ($p_data as $l_prop_key => $l_prop_val) {
                    if ($l_prop_key === 'title') {
                        continue;
                    }

                    if (!is_numeric($l_prop_key)) {
                        $l_xml .= $l_prop_key . '="' . $l_prop_val . '" ';
                    } else {
                        $l_xml .= 'key-' . $l_prop_key . '="' . $l_prop_val . '" ';
                    }
                }
            }

            $l_xml .= "/>\n";
        }

        return $l_xml;
    }

    /**
     * @param        $p_tag
     * @param        $p_data
     * @param string $p_tabs
     *
     * @return bool|string
     */
    public function iterate_sub_dialog($p_tag, $p_data, $p_tabs = "\t\t\t\t\t\t\t\t")
    {
        if (is_array($p_data) && count($p_data) > 0) {
            $l_xml = '';
            $l_xml .= $p_tabs;

            foreach ($p_data as $l_key => $l_val) {
                if (is_object($l_val) && method_exists($l_val, "get_data")) {
                    $l_data = $l_val->get_data();
                    $l_dval = @$l_data[0]["id"];

                    if (in_array($l_key, $this->m_special_leveled)) {
                        $l_puffer = $l_data;
                        $l_xml .= "\n" . $p_tabs . "<" . $l_key . ">";

                        foreach ($l_data as $l_sub_data) {
                            $l_xml .= $this->iterate_sub_dialog($l_key, [$l_sub_data], $p_tabs . "\t");
                        }
                        $l_xml .= $p_tabs . "</" . $l_key . ">";
                    } else {
                        if (is_array($l_data[0]) && count($l_data[0]) > 0) {
                            $l_xml .= "\n" . $p_tabs . "<" . $l_key;
                            foreach ($l_data[0] as $l_dkey => $l_dval) {
                                if ($l_dkey === "title") {
                                    $l_dtitle = $l_dval;
                                }

                                if (!empty($l_dval) && $l_dkey != C__CATEGORY_DATA__METHOD && $l_dkey != C__CATEGORY_DATA__HELPER) {
                                    $l_xml .= " " . $l_dkey . "=\"" . htmlspecialchars($l_dval) . "\"";
                                }
                            }
                            $l_xml .= " >";
                            $l_xml .= htmlspecialchars($l_dtitle);
                            $l_xml .= "</" . $l_key . ">";
                        }
                    }
                } elseif (is_array($l_val) && !is_null($l_val) && !empty($l_val["title"])) {
                    if (is_numeric($l_key)) {
                        $l_xml .= "\n" . $p_tabs . "<sub_" . $p_tag;
                    } else {
                        $l_xml .= "\n" . $p_tabs . "<sub_" . $l_key;
                    }

                    foreach ($l_val as $l_dkey => $l_dval) {
                        if ($l_dkey === "title") {
                            $l_dtitle = $l_dval;
                        }

                        if ($l_dkey === C__CATEGORY_DATA__HELPER && !empty($l_val[C__CATEGORY_DATA__HELPER])) {
                            if (class_exists($l_val[C__CATEGORY_DATA__HELPER])) {
                                $l_dao_class = new $l_val[C__CATEGORY_DATA__HELPER]();
                                if (method_exists($l_dao_class, $l_val[C__CATEGORY_DATA__METHOD])) {
                                    $l_method = $l_val[C__CATEGORY_DATA__METHOD];
                                    $l_dtitle = $l_dao_class->$l_method($l_dtitle);
                                }
                            }
                        }

                        if (!empty($l_dval) && $l_dkey !== C__CATEGORY_DATA__METHOD && $l_dkey !== C__CATEGORY_DATA__HELPER) {
                            $l_xml .= " " . $l_dkey . "=\"" . htmlspecialchars($l_dval) . "\"";
                        }
                    }
                    $l_xml .= ">";

                    $l_xml .= htmlspecialchars($l_dtitle);

                    if (is_numeric($l_key)) {
                        $l_xml .= "</sub_" . $p_tag . ">";
                    } else {
                        $l_xml .= "</sub_" . $l_key . ">";
                    }
                } elseif (!is_null($l_val) && $l_val != "" && !is_numeric($l_key) && !empty($l_val["title"])) {
                    $l_xml .= "\n" . $p_tabs . "<" . $l_key . ">" . htmlspecialchars($l_val) . "</" . $l_key . ">";
                }
            }
            $l_xml .= $p_tabs . "\n";

            return $l_xml;
        }

        return false;
    }

    /**
     * Parses an array and returns a copy of $this.
     *
     * @param array  $p_array
     * @param string $p_export_format
     *
     * @return string
     */
    public function parse($p_array, $p_export_format = null, $p_translate = false)
    {
        global $g_comp_session, $g_product_info;

        if (!is_array($p_array)) {
            throw new isys_exception_general('Input not an array. (isys_export_type_xml->parse())');
        }

        $l_memory = \idoit\Component\Helper\Memory::instance();

        /* Initialization */
        $l_xml = "";
        if (empty($this->m_stylesheet)) {
            $l_xml = "<?xml version=\"1.0\" encoding=\"" . $this->m_encoding . "\" standalone=\"yes\"?>\n";
        }
        $l_xml .= $this->m_stylesheet;

        $l_xml .= "<isys_export>\n";

        $this->m_export = $p_array;

        $l_xml .= "\t<head>\n" . "\t\t<datetime>" . date("Y-m-d H:i:s") . "</datetime>\n" . "\t\t<mandator language=\"" . htmlentities($g_comp_session->get_language()) .
            "\" id=\"" . htmlentities($g_comp_session->get_mandator_id()) . "\">" . $this->cdata($g_comp_session->get_mandator_name()) . "</mandator>\n" . "\t\t<type>" .
            get_class($this) . "</type>\n";

        if (!is_null($p_export_format)) {
            $l_xml .= "\t\t<format>" . $p_export_format . "</format>\n";
        }

        $l_xml .= "\t\t<version>" . $this->cdata($g_product_info["version"]) . "</version>\n" . "\t</head>\n";

        $l_xml .= "\t<objects count=\"" . (count($p_array)) . "\">\n";

        // Processing.
        foreach ($p_array as $l_object_id => $l_array) {
            if (!isset($l_array["head"])) {
                continue;
            }

            $l_value = $l_array["head"];

            try {
                $l_memory->outOfMemoryBreak(2048000);
            } catch (Exception $e) {
                isys_notify::warning($e->getMessage(), ['sticky' => true]);
                isys_application::instance()->logger->warning($e->getMessage());
                break;
            }

            $l_xml .= "\t\t<object>\n";

            $l_title = $this->cdata($l_value["title"]);
            $l_type_title = $this->cdata($l_value["type"]["title"]);
            $l_sysid = $this->cdata($l_value["sysid"]);
            $l_description = $this->cdata($l_value["description"]);

            $l_xml .= "\t\t\t<id>" . $l_value["id"] . "</id>\n" . "\t\t\t<title>" . $l_title . "</title>\n" . "\t\t\t<sysid>" . $l_sysid . "</sysid>\n" .
                "\t\t\t<created by=\"" . $l_value["created_by"] . "\">" . $l_value["created"] . "</created>\n" . "\t\t\t<updated by=\"" . $l_value["updated_by"] . "\">" .
                $l_value["updated"] . "</updated>\n" . "\t\t\t<type " . "id=\"" . $l_value["type"]["id"] . "\" " . "const=\"" . $l_value["type"]["const"] . "\" " .
                "title_lang=\"" . $l_value["type"]["title_lang"] . "\" " . "group=\"" . $l_value["type"]["group"] . "\" " . "sysid_prefix=\"" .
                $l_value["type"]["sysid_prefix"] . "\">" . $l_type_title . "</type>\n" . "\t\t\t<status lc_title='" . $this->get_status_lc($l_value["status"]) . "'>" .
                $l_value["status"] . "</status>\n" . "\t\t\t<cmdb_status>" . $l_value["cmdb_status"] . "</cmdb_status>\n" . "\t\t\t<description>" . $l_description .
                "</description>\n" . "\n";

            $l_xml .= "\t\t\t" . "<data>\n";

            foreach ([
                         C__CMDB__CATEGORY__TYPE_GLOBAL,
                         C__CMDB__CATEGORY__TYPE_SPECIFIC,
                         C__CMDB__CATEGORY__TYPE_CUSTOM
                     ] as $l_cat) {
                if (isset($l_array[$l_cat]) && is_array($l_array[$l_cat])) {
                    $l_xml .= $this->process($l_array[$l_cat], $l_cat, $l_value["status"], $p_translate);
                }
            }

            $l_xml .= "\t\t\t" . "</data>\n";

            $l_xml .= "\t\t</object>\n";
        }

        $l_xml .= "\t</objects>\n";

        $l_xml .= "</isys_export>";

        $this->set_formatted_export($l_xml);

        return $this;
    }

    /**
     * Print CDATA Tag if needed
     *
     * @param string $p_value
     *
     * @return string
     */
    public function cdata($p_value)
    {
        if (!preg_match("/^[\W]+$/", $p_value)) {
            return '<![CDATA[' . $p_value . ']]>';
        }

        return htmlspecialchars($p_value);
    }

    /**
     * Processes the category export
     *
     * @param array $p_sub
     * @param int   $p_cattype Category type's identifier
     * @param int   $p_object_status
     *
     * @return string
     */
    private function process($p_sub, $p_cattype = null, $p_object_status = null, $p_translate = false)
    {
        $isBrowserFirefox = strpos(isys_application::instance()->container->get('request')->headers->get('User-Agent'), 'Firefox') !== false;

        /* Create XML .. */
        $l_xml = '';

        foreach ($p_sub as $l_catid => $l_catdata) {
            $l_cathead = $l_catdata['head'];
            unset($l_catdata['head']);

            // if array with no key then continue with next category
            if (key($l_catdata) === '') {
                continue;
            }

            $l_xml .= "\t\t\t\t" . '<category';

            if (is_array($l_cathead) && count($l_cathead) > 0) {
                foreach ($l_cathead as $l_cathead_key => $l_cathead_value) {
                    if ($l_cathead_key === 'title') {
                        if ($p_translate === true) {
                            $l_cathead_value = isys_application::instance()->container->get('language')
                                ->get($l_cathead_value);
                        }
                    }
                    $l_xml .= ' ' . $l_cathead_key . '="' . htmlspecialchars($l_cathead_value) . '"';
                }
            }

            $l_xml .= ">\n";

            foreach ($l_catdata as $l_category_id => $l_properties) {
                // Category header:
                $l_xml .= "\t\t\t\t\t<cat_data";

                if (!empty($l_category_id)) {
                    $l_xml .= ' data_id="' . $l_category_id . '"';
                }

                //$l_xml .= ' ref_id="' . $l_cathead['ref_id'] . '"';

                $l_xml .= ">\n";

                // New behavior, but maybe broken because some code from old behavior (see below) is missing. Test it!
                // Iterate through category's properties.
                foreach ($l_properties as $l_property) {
                    $l_title = null;
                    $l_tag = preg_replace('/\W/', '', $l_property[C__DATA__TAG]);
                    if (isset($l_property[C__DATA__TITLE])) {
                        $l_title = $l_property[C__DATA__TITLE];
                        if ($p_translate === true) {
                            $l_title = isys_application::instance()->container->get('language')
                                ->get($l_title);
                        }
                    }

                    switch (gettype($l_property[C__DATA__VALUE])) {
                        case 'string':
                            // @see  ID-4767  Duplicating (and probably exporting) objects in firefox lost all formatting of HTML data (WYSIWYG data and so on).
                            if ($isBrowserFirefox && empty($_POST)) {
                                // @see  ID-826  Fixes the print view in firefox. The print view is a pure GET request without POST data.
                                $l_property[C__DATA__VALUE] = html_entity_decode(filter_var($l_property[C__DATA__VALUE], FILTER_SANITIZE_STRING));
                            }
                            if (!isset($l_property[C__DATA__VALUE]) || $l_property[C__DATA__VALUE] === '') {
                                $l_xml .= "\t\t\t\t\t\t";
                                $l_xml .= '<' . $l_tag;
                                if (isset($l_title)) {
                                    $l_xml .= ' title="' . htmlspecialchars($l_title) . '"';
                                }
                                $l_xml .= "/>\n";
                                break;
                            }
                            $l_xml .= "\t\t\t\t\t\t";
                            $l_xml .= '<' . $l_tag;
                            if (isset($l_title)) {
                                $l_xml .= ' title="' . htmlspecialchars($l_title) . '"';
                            }
                            if (isset($l_property['id'])) {
                                $l_xml .= ' id="' . $l_property['id'] . '"';
                            }
                            $l_xml .= '>';
                            if ($l_tag == 'description' || strstr($l_property[C__DATA__VALUE], "\n")) {
                                $l_xml .= '<![CDATA[' . $l_property[C__DATA__VALUE] . ']]>';
                            } else {
                                $l_xml .= htmlspecialchars($l_property[C__DATA__VALUE]);
                            }
                            $l_xml .= '</' . $l_tag . ">\n";
                            break;

                        case 'object':
                            switch (get_class($l_property[C__DATA__VALUE])) {
                                case 'isys_export_data':
                                    $l_data = $l_property[C__DATA__VALUE]->get_data();
                                    $l_parent = false;

                                    if (is_array($l_data) && count($l_data) > 0) {
                                        foreach ($l_data as $l_key => $l_item) {
                                            if (is_numeric($l_key)) {
                                                if (!$l_parent) {
                                                    $l_xml .= "\t\t\t\t\t\t" . "<" . $l_tag . " title=\"" . htmlspecialchars($l_title) . "\">\n";
                                                }

                                                $l_parent = true;

                                                $l_xml .= $this->iterate_dialog('sub_' . $l_tag, $l_item, "\t\t\t\t\t\t\t", $l_title);
                                            } else {
                                                $l_key = preg_replace('/\W/', '', $l_key);
                                                $l_xml .= "\t\t\t\t\t\t" . '<' . $l_key;

                                                if (isset($l_title)) {
                                                    $l_xml .= ' title="' . htmlspecialchars($l_title) . '"';
                                                }

                                                $l_xml .= ">\n";

                                                if (is_array($l_item)) {
                                                    foreach ($l_item as $l_subitem) {
                                                        $l_xml .= $this->iterate_dialog($l_key, $l_subitem, "\t\t\t\t\t\t\t");
                                                    }
                                                }
                                                $l_xml .= "\t\t\t\t\t\t" . '</' . $l_key . ">\n";
                                            }
                                        }
                                    } else {
                                        $l_xml .= "\t\t\t\t\t\t";
                                        $l_xml .= '<' . $l_tag;
                                        if (isset($l_title)) {
                                            $l_xml .= ' title="' . htmlspecialchars($l_title) . '"';
                                        }
                                        $l_xml .= "/>\n";
                                    }

                                    if ($l_parent) {
                                        $l_xml .= "\t\t\t\t\t\t" . "</" . $l_tag . ">\n";
                                    }

                                    break;
                            }
                            break;

                        case 'array':
                            $l_xml .= $this->iterate_dialog($l_property[C__DATA__TAG], $l_property[C__DATA__VALUE], "\t\t\t\t\t\t", $l_title);
                            break;

                        case 'boolean':
                        default:
                            if (strpos($l_tag, 'html_') === 0) {
                                if ($isBrowserFirefox && empty($_POST)) {
                                    // @see  ID-826  Fixes the print view in firefox. The print view is a pure GET request without POST data.
                                    $l_title = html_entity_decode(filter_var($l_title, FILTER_SANITIZE_STRING));
                                }
                                $l_xml .= "\t\t\t\t\t\t";
                                $l_xml .= '<' . $l_tag . '>';
                                $l_xml .= '<![CDATA[' . $l_title . ']]>';
                                $l_xml .= '</' . $l_tag . ">\n";
                                break;
                            }
                            // Just add the tag with title
                            $l_xml .= "\t\t\t\t\t\t";
                            $l_xml .= '<' . $l_tag;
                            if (isset($l_title)) {
                                $l_xml .= ' title="' . htmlspecialchars($l_title) . '"';
                            }
                            $l_xml .= "/>\n";
                            break;
                    }
                }

                $l_xml .= "\t\t\t\t\t" . "</cat_data>\n";
            }

            $l_xml .= "\t\t\t\t" . "</category>\n\n";
        }

        return $l_xml;
    }

    /**
     * Recurse through array data
     *
     * @param array  $p_data
     * @param string $p_tabs
     *
     * @return string
     */
    private function recurse($p_data, $p_tabs = "\t", $p_object_status = C__RECORD_STATUS__NORMAL)
    {
        $l_xml = "";
        $l_attributes = "";

        if (is_array($p_data)) {
            foreach ($p_data as $l_ptag => $l_pval) {
                if (!is_array(($l_pval))) {
                    if (!is_null($l_pval) && $l_pval != "") {
                        if ($l_ptag == "value") {
                            $l_xml .= $this->cdata($l_pval);
                        } else {
                            if ($l_ptag == "id" && $p_object_status == C__RECORD_STATUS__TEMPLATE) {
                                continue;
                            }
                            $l_xml .= $p_tabs;
                            $l_xml .= "<" . $l_ptag . $l_attributes . ">" . $this->cdata($l_pval) . "</" . $l_ptag . ">\n";
                        }
                    }
                } else {
                    if (isset($l_pval["attributes"])) {
                        foreach ($l_pval["attributes"] as $l_akey => $l_aval) {
                            if ($l_akey == "id" && $p_object_status == C__RECORD_STATUS__TEMPLATE) {
                                continue;
                            }

                            if (!empty($l_aval)) {
                                $l_attributes .= " " . $l_akey . "=\"" . htmlspecialchars($l_aval) . "\"";
                            }
                        }
                        unset($l_pval["attributes"]);
                    }

                    if (!is_null($l_pval) || !empty($l_attributes)) {
                        $l_xml .= $p_tabs . "<" . $l_ptag . $l_attributes . ">";
                        $l_xml .= $this->recurse($l_pval, $p_tabs . "\t", $p_object_status);
                        $l_xml .= "</" . $l_ptag . ">\n";
                    }

                    $l_attributes = "";
                }
            }
        }

        return $l_xml;
    }

    /**
     * @param $p_const
     *
     * @return mixed
     */
    private function get_status_lc($p_const)
    {
        switch ($p_const) {
            case C__RECORD_STATUS__BIRTH:
                return isys_application::instance()->container->get('language')->get('LC__CMDB__RECORD_STATUS__BIRTH');
            default:
            case C__RECORD_STATUS__NORMAL:
                return isys_application::instance()->container->get('language')->get('LC__CMDB__RECORD_STATUS__NORMAL');
            case C__RECORD_STATUS__ARCHIVED:
                return isys_application::instance()->container->get('language')->get('LC__CMDB__RECORD_STATUS__ARCHIVED');
            case C__RECORD_STATUS__DELETED:
                return isys_application::instance()->container->get('language')->get('LC__RECORD_STATUS__Deleted');
        }
    }

    /**
     * isys_export_type_xml constructor.
     *
     * @param null $p_encoding
     */
    public function __construct($p_encoding = null)
    {
        if ($p_encoding !== null) {
            $this->m_encoding = $p_encoding;
        }
    }
}
