<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\PasswordProperty;

/**
 * i-doit
 *
 * DAO: global category for passwords
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_password extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'password';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__PASSWD';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__PASSWD;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        if (strpos(base64_decode($p_data['password']), isys_helper_crypt::DELIMITER) === false) {
            $p_data['password'] = isys_helper_crypt::encrypt($p_data['password']);
        }

        return parent::create_data($p_data);
    }

    /**
     * Decrypt password, when using dynamic property and password.decrypt.in-export-import is true
     *
     * @param array $p_row
     *
     * @return mixed|string
     */
    public function decryptPassword(array $p_row)
    {
        if (!empty($p_row['isys_catg_password_list__password']) && isys_tenantsettings::get('password.decrypt.in-export-import', 0)) {
            return isys_helper_crypt::decrypt($p_row['isys_catg_password_list__password']);
        }

        return $p_row['isys_catg_password_list__password'];
    }

    /**
     * @inheritdoc
     */
    protected function dynamic_properties()
    {
        return [
            '_password' => new DynamicProperty(
                'LC__LOGIN__PASSWORD',
                'isys_catg_password_list__password',
                'isys_catg_password_list',
                [
                    $this,
                    'decryptPassword'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_password_list__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__PASSWORD__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'username'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__LOGIN__USERNAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Username'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_password_list__username'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__PASSWORD__USERNAME'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'password'    => (new PasswordProperty(
                'C__CATG__PASSWORD__PASSWORD',
                'LC__LOGIN__PASSWORD',
                'isys_catg_password_list__password',
                'isys_catg_password_list'
            )),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_password_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__PASSWD', 'C__CATG__PASSWD')
                ],
            ])
        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['password'] = isys_helper_crypt::encrypt($p_data['password']);

        return parent::save_data($p_category_data_id, $p_data);
    }
}
