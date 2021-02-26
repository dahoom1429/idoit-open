<?php

/**
 * i-doit
 *
 * DAO: global category for JDisc custom attributes.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_jdisc_ca extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'jdisc_ca';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Wrapper for create data
     *
     * @param array $p_data
     *
     * @return mixed|void
     */
    public function create_data($p_data)
    {
        $l_attributes = $this->get_attribute_types();

        if (isset($_POST['C__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE'])) {
            switch ($l_attributes[$p_data['attribute_type']]['isys_jdisc_ca_type__const']) {
                case 'C__JDISC__CA_TYPE__CURRENCY':
                    $p_data['attribute_content'] = ((float)$p_data['attribute_content'] * 100);
                    break;
                case 'C__JDISC__CA_TYPE__DATE':
                    $p_data['attribute_content'] = date('Y-m-d', strtotime($p_data['attribute_content']));
                    break;
            }
        }
        parent::create_data($p_data);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'attribute'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__ATTRIBUTE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Attribute title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_jdisc_ca_list__title',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__JDISC__CUSTOM_ATTRIBUTES__TITLE',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => true
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ]
            ]),
            'attribute_content' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__CONTENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Attribute content'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_jdisc_ca_list__content',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__JDISC__CUSTOM_ATTRIBUTES__CONTENT',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => true
                ]
            ]),
            'attribute_type'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Attribute content'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_jdisc_ca_list__isys_jdisc_ca_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_jdisc_ca_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_jdisc_ca_type',
                        'isys_jdisc_ca_type__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_jdisc_ca_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => true
                ]
            ]),
            'attribute_folder'  => array_replace_recursive(isys_cmdb_dao_category_pattern::textarea(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__FOLDER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Attribute folder'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_jdisc_ca_list__folder',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__JDISC__CUSTOM_ATTRIBUTES__FOLDER',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => true
                ]
            ]),
            'description'       => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_jdisc_ca_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LDAP_DN', 'C__CATG__LDAP_DN')
                ]
            ])
        ];
    }

    /**
     * Wrapper for save_data
     *
     * @param int   $p_category_id
     * @param array $p_data
     *
     * @return bool|void
     */
    public function save_data($p_category_id, $p_data)
    {
        $l_attributes = $this->get_attribute_types();

        if (isset($_POST['C__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE'])) {
            switch ($l_attributes[$p_data['attribute_type']]['isys_jdisc_ca_type__const']) {
                case 'C__JDISC__CA_TYPE__CURRENCY':
                    $p_data['attribute_content'] = ((float)$p_data['attribute_content'] * 100);
                    break;
                case 'C__JDISC__CA_TYPE__DATE':
                    $p_data['attribute_content'] = date('Y-m-d', strtotime($p_data['attribute_content']));
                    break;
            }
        }
        parent::save_data($p_category_id, $p_data);
    }

    /**
     * Retrieves all custom attribute types
     *
     * @return mixed
     */
    public function get_attribute_types()
    {
        $l_sql = 'SELECT * FROM isys_jdisc_ca_type';
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row['isys_jdisc_ca_type__id']] = $l_row;
        }

        return $l_return;
    }
}
