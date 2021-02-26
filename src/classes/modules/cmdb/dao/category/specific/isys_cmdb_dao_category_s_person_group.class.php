<?php

/**
 * i-doit
 *
 * DAO: specific category for person groups. Just the category constant the rest will be inherited from the
 * isys_cmdb_dao_category_s_person_group_master
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_group extends isys_cmdb_dao_category_s_person_group_master
{
    /**
     * Overwrites constant string
     *
     * @var string
     */
    protected $m_category_const = 'C__CATS__PERSON_GROUP';

    /**
     * Overwrites constant
     *
     * @var int
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__PERSON_GROUP;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_TITLE'
                ]
            ]),
            'email_address' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_EMAIL_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'EMail'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_mail_addresses_list__title  FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list', 'isys_catg_mail_addresses_list__id', 'isys_catg_mail_addresses_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' isys_catg_mail_addresses_list__primary = 1'])),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_mail_addresses_list', 'LEFT', 'isys_catg_mail_addresses_list__isys_obj__id',
                            'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_EMAIL_ADDRESS'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'phone'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Phone'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__phone'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_PHONE'
                ]
            ]),
            'ldap_group'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PERSON_GROUPS__LDAP_MAPPING',
                    C__PROPERTY__INFO__DESCRIPTION => 'LDAP Group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__ldap_group'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_LDAP'
                ]
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP', 'C__CATS__PERSON_GROUP')
                ]
            ])
        ];
    }

    /**
     * @return array
     * @throws isys_exception_database
     */
    public function getAllGroupsNotPredefined(): array
    {
        $sql = "SELECT isys_obj__id as id
                FROM isys_obj AS o
                LEFT JOIN isys_obj_type AS t ON t.isys_obj_type__id = o.isys_obj__isys_obj_type__id
                WHERE (o.isys_obj__const IS NULL)
                AND t.isys_obj_type__isysgui_cats__id = 
                (SELECT isysgui_cats__id FROM isysgui_cats WHERE isysgui_cats__const = " .
                    $this->convert_sql_text('C__CATS__PERSON_GROUP') . ")  
                AND (o.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $result = $this->retrieve($sql);
        $returnArray = [];
        while ($group = $result->get_row()) {
            $returnArray[] = $group['id'];
        }

        return $returnArray;
    }
}
