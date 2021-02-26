<?php

/**
 * i-doit
 *
 * CMDB DAO: Global category for "Last login user".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_last_login_user extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'last_login_user';

    /**
     * Defines, if the category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Import-Handler for this category.
     *
     * @param   array   $p_data
     * @param   integer $p_obj_id
     * @param   boolean $p_operating_system
     *
     * @return  bool
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_obj_id = null)
    {
        if ($p_data && isset($p_data['user'])) {
            $data = $this->get_data_by_object($p_obj_id);

            if (!$data->count()) {
                $id = $this->create_connector('isys_catg_last_login_user_list', $p_obj_id);
            } else {
                $row = $data->get_row();
                $id = $row['isys_catg_last_login_user_list__id'];
            }

            $this->save_data($id, [
                'last_login' => $p_data['user']
            ]);

            return true;
        }

        return false;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'last_login'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__LAST_LOGIN_USER__LAST_LOGIN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Last login'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_last_login_user_list__last_login'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LAST_LOGIN_USER__LAST_LOGIN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => true,
                    ]
                ]
            ]),
            'type'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__LAST_LOGIN_USER__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_last_login_user_list__type'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__LAST_LOGIN_USER__TYPE'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_last_login_user_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LAST_LOGIN_USER', 'C__CATG__LAST_LOGIN_USER')
                ]
            ])
        ];
    }

    /**
     * Synchronize category content with $p_data.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_arr = [
                'last_login' => $p_category_data['properties']['last_login'][C__DATA__VALUE],
                'type'       => $p_category_data['properties']['type'][C__DATA__VALUE],
            ];

            if (!$p_category_data['data_id']) {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_last_login_user_list', $p_object_id);
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        parent::save_data($p_category_data['data_id'], $l_arr);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}