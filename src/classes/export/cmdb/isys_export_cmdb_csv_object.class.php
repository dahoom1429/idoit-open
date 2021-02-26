<?php

class isys_export_cmdb_csv_object extends isys_export_cmdb
{

    /**
     * Export object(s)
     *
     * @param mixed $p_object_ids
     *
     * @return isys_export_cmdb_object
     */
    public function export($p_object_ids, $l_export_class = "", $p_record_status = C__RECORD_STATUS__NORMAL, $p_duplicate = false)
    {
        global $g_comp_database;

        $this->m_export = [];

        $l_export_obj = new $l_export_class($g_comp_database);

        // Header for csv file
        $l_header_arr = $l_export_obj->get_header();

        $this->m_export = $l_export_obj->export($p_object_ids);

        return $this;
    }

    /**
     * Parses output data with the export formatter
     *
     * @param array $p_data
     */
    public function parse($p_data = null, $p_stylesheet = null)
    {

        if (is_array($p_data)) {
            return $this->m_export_formatter->parse($p_data);
        } else if (is_array($this->m_export)) {
            return $this->m_export_formatter->parse($this->m_export);
        } else {
            throw new Exception("Wrong input format. Data must be an array.");
        }
    }

    public function __construct($p_export_type = "isys_export_type_xml", &$p_database = null)
    {
        parent::__construct($p_export_type, $p_database);
    }

}

?>
