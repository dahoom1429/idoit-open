<?php

/**
 * i-doit
 *
 * DAO: global category for smartcard certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_smartcard_certificate extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'smartcard_certificate';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'cardnumber'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__CARDNUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cardnumber'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__cardnumber',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__CARDNUMBER',
                ]
            ]),
            'barring_password' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__BARRING_PASSWORD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Barring password'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__barring_password',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__BARRING_PASSWORD',
                ]
            ]),
            'pin_nr'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__PIN_NR',
                    C__PROPERTY__INFO__DESCRIPTION => 'PIN-Nr.'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__pin_number',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__PIN_NR',
                ]
            ]),
            'reference'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__REFERENCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Reference'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__reference',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__REFERENCE',
                ]
            ]),
            'expires_on'       => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__EXPIRES_ON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Expires on'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__expires_on',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__EXPIRES_ON',
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SMARTCARD_CERTIFICATE', 'C__CATG__SMARTCARD_CERTIFICATE')
                ]
            ])
        ];
    }

}

?>