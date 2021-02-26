<?php

/**
 * i-doit
 *
 * JDisc custom attributes DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.3
 */
class isys_jdisc_dao_custom_attributes extends isys_jdisc_dao_data
{

    protected $m_attribute_types = [];

    /**
     * Method for receiving the custom attributes, assigned to a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     * @param   boolean $p_all_clusters If set to true we create objects for every cluster JDisc could find.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_custom_attributes_by_device($p_id, $p_raw = false)
    {
        $l_return = [];
        $l_already_imported = [];

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao                isys_cmdb_dao
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        $l_sql = 'SELECT da.*, ca.*, LOWER(atl.name) AS attr_type FROM deviceattribute AS da
			INNER JOIN customattribute AS ca ON ca.id = da.customattributeid
			INNER JOIN attributetypelookup AS atl ON atl.id = ca.attributetype
			WHERE da.deviceid = ' . $l_dao->convert_sql_id($p_id) . '
			ORDER BY ca.ordernumber ASC';

        $l_res = $this->fetch($l_sql);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' custom attributes rows');

        // Testing case
        //$l_row['attribute_name'] = 'testing_cluster';
        //$l_row['id'] = '1';
        //$l_row['attribute_value'] = '5';
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_row['parentfolder'] = null;
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                // get parent folders recursive
                if ($l_row['parentid'] !== null) {
                    $l_row['parentfolder'] = $this->get_parents_as_string_recursive($l_row['parentid']);
                }

                $l_return[] = $this->prepare_custom_attribute($l_row);
            }

        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__JDISC_CUSTOM_ATTRIBUTES'),
                'const'             => 'C__CATG__JDISC_CA',
                'category_type'     => defined_or_default('C__CATG__JDISC_CA'),
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_custom_attribute(array $p_data)
    {
        if (!empty($p_data)) {
            switch ($p_data['attr_type']) {
                case 'text':
                    $p_data['attribute_value'] = $p_data['stringvalue'];
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__TEXT';
                    break;
                case 'multiline text':
                    $p_data['attribute_value'] = $p_data['stringvalue'];
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__MULTITEXT';
                    break;
                case 'enumeration':
                    $p_data['attribute_value'] = $p_data['stringvalue'];
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__ENUMERATION';
                    break;
                case 'date':
                    $p_data['attribute_value'] = date('Y-m-d', strtotime($p_data['timestampvalue']));
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__DATE';
                    break;
                case 'time':
                    $p_data['attribute_value'] = date('H:i', strtotime($p_data['timestampvalue']));
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__TIME';
                    break;
                case 'integer':
                    $p_data['attribute_value'] = $p_data['longvalue'];
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__INTEGER';
                    break;
                case 'currency':
                    $p_data['attribute_value'] = $p_data['longvalue'];
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__CURRENCY';
                    break;
                case 'document':
                    $p_data['attribute_value'] = null;
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__DOCUMENT';
                    break;
                default:
                    $p_data['attribute_value'] = null;
                    $l_attr_type_const = 'C__JDISC__CA_TYPE__TEXT';
                    break;
            }
            $p_data['attribute_type'] = $this->m_attribute_types[$l_attr_type_const][1];

            return [
                'data_id'    => null,
                'properties' => [
                    'attribute_type'    => [
                        'tag'        => 'attribute_type',
                        'value'      => $p_data['attribute_type'],
                        'title_lang' => $p_data['attribute_type'],
                        'title'      => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE',
                    ],
                    'attribute'         => [
                        'tag'   => 'attribute',
                        'value' => $p_data['name'],
                        'title' => 'LC__CMDB__CATG__JDISC__ATTRIBUTES__TITLE'
                    ],
                    'attribute_content' => [
                        'tag'   => 'attribute_content',
                        'value' => $p_data['attribute_value'],
                        'title' => 'LC__CMDB__CATG__JDISC__ATTRIBUTES__CONTENT'
                    ],
                    'attribute_folder'  => [
                        'tag'   => 'attribute_folder',
                        'value' => $p_data['parentfolder'],
                        'title' => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__FOLDER'
                    ]
                ]
            ];
        }
    }

    /**
     * Get parent folders recursive
     *
     * @param integer $p_parentid
     *
     * @return string
     */
    private function get_parents_as_string_recursive($p_parentid)
    {
        $l_sql = 'SELECT id, name, parentid FROM customattribute WHERE id = ' . $this->convert_sql_id($p_parentid);
        $l_res = $this->fetch($l_sql);
        $l_row = $this->m_pdo->fetch_row_assoc($l_res);
        $l_return = $l_row['name'];
        if (!empty($l_row['parentid'])) {
            $l_return .= ' >> ' . $this->get_parents_as_string_recursive($l_row['parentid']);
        }

        return $l_return;
    }

    /**
     * Sets attribute types
     */
    private function set_attribute_types()
    {
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        $l_sql = 'SELECT * FROM isys_jdisc_ca_type';
        $l_res = $l_dao->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $this->m_attribute_types[$l_row['isys_jdisc_ca_type__const']] = [
                $l_row['isys_jdisc_ca_type__id'],
                $l_row['isys_jdisc_ca_type__title']
            ];
        }
    }

    /**
     * Constructor
     *
     * @param   isys_component_database     $p_db Database component
     * @param   isys_component_database_pdo $p_pdo
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        parent::__construct($p_db, $p_pdo);
        $this->set_attribute_types();
    }
}

?>
