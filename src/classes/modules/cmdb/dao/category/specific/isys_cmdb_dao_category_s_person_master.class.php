<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for master persons
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_master extends isys_cmdb_dao_category_s_person
{
    /**
     * Caching for our list.
     *
     * @var  array
     */
    protected static $m_obj_cache = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_master';

    /**
     * Category's table.
     *
     * @var  string
     */
    protected $m_table = 'isys_cats_person_list';

    /**
     * Category's UI class.
     *
     * @var  string
     */
    protected $m_ui = 'isys_cmdb_ui_category_s_person';

    /**
     * Object type for newly created persons.
     *
     * @var  string
     */
    private $m_default_person_object_type = 'C__OBJTYPE__PERSON';

    /**
     *
     * @param   string $p_type
     *
     * @return  $this
     */
    public function set_default_person_object_type($p_type = 'C__OBJTYPE__PERSON')
    {
        $this->m_default_person_object_type = $p_type;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPersonObjectType(): string
    {
        return $this->m_default_person_object_type;
    }

    /**
     *
     * @param   isys_request $p_request
     *
     * @return  array
     */
    public function callback_property_organisation(isys_request $p_request)
    {
        $l_org = [];

        $l_res = $this->get_objects_by_type_id(defined_or_default('C__OBJTYPE__ORGANIZATION'), C__RECORD_STATUS__NORMAL);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_org[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
            }
        }

        return $l_org;
    }

    /**
     * Checks if a user exist.
     *
     * @param   string $p_username
     *
     * @return  mixed
     */
    public function exists($p_username)
    {
        $l_user = $this->get_person_by_username($p_username);

        if ($l_user->num_rows() > 0) {
            return $l_user->get_row_value('isys_cats_person_list__isys_obj__id');
        }

        return false;
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level         Level to save, default 0.
     * @param   integer & $p_intOldRecStatus __status of record before update.
     *
     * @return  integer
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_person_list__status"];

        $l_list_id = $l_catdata["isys_cats_person_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_person_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                $_POST["C__CONTACT__PERSON_FIRST_NAME"],
                $_POST["C__CONTACT__PERSON_LAST_NAME"],
                $_POST["C__CONTACT__PERSON_MAIL_ADDRESS"],
                $_POST["C__CONTACT__PERSON_PHONE_COMPANY"],
                $_POST["C__CONTACT__PERSON_PHONE_HOME"],
                $_POST["C__CONTACT__PERSON_PHONE_MOBILE"],
                $_POST["C__CONTACT__PERSON_FAX"],
                $_POST["C__CONTACT__PERSON_DEPARTMENT"],
                $_POST["C__CONTACT__PERSON_ASSIGNED_ORGANISATION__HIDDEN"],
                ((empty($_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()])) ? $_POST["C__CMDB__CAT__COMMENTARY_" .
                $this->get_category_type() . defined_or_default('C__CATS__PERSON')] : $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]),
                null,
                '',
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CONTACT__PERSON_PERSONNEL_NUMBER"],
                $_POST["C__CONTACT__PERSON_ACADEMIC_DEGREE"],
                $_POST["C__CONTACT__PERSON_FUNKTION"],
                $_POST["C__CONTACT__PERSON_SERVICE_DESIGNATION"],
                $_POST["C__CONTACT__PERSON_CITY"],
                $_POST["C__CONTACT__PERSON_ZIP_CODE"],
                $_POST["C__CONTACT__PERSON_STREET"],
                $_POST["C__CONTACT__PERSON_PAGER"],
                $_POST["C__CONTACT__PERSON_SALUTATION"]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            // Save custom properties
            $this->save_custom_properties($l_list_id, $_POST);
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Save method.
     *
     * @param   int        $p_id
     * @param   string     $p_firstname
     * @param   string     $p_lastname
     * @param   string     $p_mail
     * @param   string     $p_phoneOffice
     * @param   string     $p_phonePrivate
     * @param   string     $p_phoneCell
     * @param   string     $p_fax
     * @param   string     $p_department
     * @param   int|string $p_organization
     * @param   string     $p_description
     * @param   string     $p_ldap_server
     * @param   string     $p_ldap_dn
     * @param   int        $p_status
     * @param   string     $p_personnel_number
     * @param   string     $p_academic_degree
     * @param   string     $p_function
     * @param   string     $p_service_designation
     * @param   string     $p_city
     * @param   string     $p_zip_code
     * @param   string     $p_street
     * @param   string     $p_pager
     * @param   string     $p_salutation
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     */
    public function save(
        $p_id,
        $p_firstname,
        $p_lastname,
        $p_mail,
        $p_phoneOffice,
        $p_phonePrivate,
        $p_phoneCell,
        $p_fax,
        $p_department = "",
        $p_organization = null,
        $p_description = "",
        $p_ldap_server = null,
        $p_ldap_dn = "",
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_personnel_number = '',
        $p_academic_degree = "",
        $p_function = "",
        $p_service_designation = "",
        $p_city = "",
        $p_zip_code = "",
        $p_street = '',
        $p_pager = '',
        $p_salutation = ''
    ) {
        if (!($p_id > 0)) {
            return false;
        }

        $changes = [
            'isys_cats_person_list__status' => $this->convert_sql_id($p_status),
            'isys_obj__title'               => []
        ];

        $organizationWasGiven = $p_organization !== null;
        $organizationConnectionId = null;
        $l_dao = new isys_cmdb_dao_connection($this->m_db);
        $l_old_data = $this->get_data($p_id)->get_row();

        // @see  ID-6903  Don't do anything with the organization, if it's value is "null".
        if ($organizationWasGiven) {
            if (!is_numeric($p_organization)) {
                $p_organization = (int)$this->get_obj_id_by_title(trim($p_organization), defined_or_default('C__OBJTYPE__ORGANIZATION'));
            }

            if ($l_old_data) {
                if (!$this->obj_exists($p_organization)) {
                    $p_organization = null;
                }

                if (empty($l_old_data[$this->m_table . '__isys_connection__id'])) {
                    $organizationConnectionId = $l_dao->add_connection($p_organization);
                } else {
                    $organizationConnectionId = $l_dao->update_connection($l_old_data[$this->m_table . '__isys_connection__id'], $p_organization);
                }
            }
        }

        if ($p_firstname !== null) {
            $name = trim($p_firstname);
            $changes['isys_cats_person_list__first_name'] = $this->convert_sql_text($name);
            $changes['isys_obj__title'][] = $name;
        }
        if ($p_lastname !== null) {
            $name = trim($p_lastname);
            $changes['isys_cats_person_list__last_name'] = $this->convert_sql_text($name);
            $changes['isys_obj__title'][] = $name;
        }
        if ($p_phoneOffice !== null) {
            $changes['isys_cats_person_list__phone_company'] = $this->convert_sql_text($p_phoneOffice);
        }
        if ($p_phoneCell !== null) {
            $changes['isys_cats_person_list__phone_mobile'] = $this->convert_sql_text($p_phoneCell);
        }
        if ($p_phonePrivate !== null) {
            $changes['isys_cats_person_list__phone_home'] = $this->convert_sql_text($p_phonePrivate);
        }
        if ($p_fax !== null) {
            $changes['isys_cats_person_list__fax'] = $this->convert_sql_text($p_fax);
        }
        if ($p_personnel_number !== null) {
            $changes['isys_cats_person_list__personnel_number'] = $this->convert_sql_text($p_personnel_number);
        }
        if ($p_department !== null) {
            $changes['isys_cats_person_list__department'] = $this->convert_sql_text($p_department);
        }
        if ($organizationConnectionId !== null) {
            $changes['isys_cats_person_list__isys_connection__id'] = $this->convert_sql_id($organizationConnectionId);
        }
        if ($p_description !== null) {
            $changes['isys_cats_person_list__description'] = $this->convert_sql_text($p_description);
        }
        if ($p_salutation !== null) {
            $changes['isys_cats_person_list__salutation'] = $this->convert_sql_text($p_salutation);
        }
        if ($p_academic_degree !== null) {
            $changes['isys_cats_person_list__academic_degree'] = $this->convert_sql_text($p_academic_degree);
        }
        if ($p_function !== null) {
            $changes['isys_cats_person_list__function'] = $this->convert_sql_text($p_function);
        }
        if ($p_service_designation !== null) {
            $changes['isys_cats_person_list__service_designation'] = $this->convert_sql_text($p_service_designation);
        }
        if ($p_city !== null) {
            $changes['isys_cats_person_list__city'] = $this->convert_sql_text($p_city);
        }
        if ($p_zip_code !== null) {
            $changes['isys_cats_person_list__zip_code'] = $this->convert_sql_text($p_zip_code);
        }
        if ($p_street !== null) {
            $changes['isys_cats_person_list__street'] = $this->convert_sql_text($p_street);
        }
        if ($p_pager !== null) {
            $changes['isys_cats_person_list__pager'] = $this->convert_sql_text($p_pager);
        }
        if (is_array($changes['isys_obj__title']) && !empty($changes['isys_obj__title'])) {
            $changes['isys_obj__title'] = $this->convert_sql_text(implode(' ', $changes['isys_obj__title']));
        } else {
            unset($changes['isys_obj__title']);
        }

        if ($p_ldap_dn !== '') {
            $changes['isys_cats_person_list__ldap_dn'] = $this->convert_sql_text($p_ldap_dn);
            if (count(isys_ldap_dao::instance($this->m_db)->get_data($p_ldap_server)) === 1) {
                $changes['isys_cats_person_list__isys_ldap__id'] = $this->convert_sql_id($p_ldap_server);
            }
            isys_cmdb_dao_category_g_ldap_dn::instance($this->m_db)
                ->save_single_value($this->get_object_id_by_category_id($p_id), [
                    'title'  => $p_ldap_dn,
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
        }

        $changes = array_map(function ($k, $v) {
            return $k . ' = ' . $v;
        }, array_keys($changes), $changes);

        $l_sql = 'UPDATE isys_cats_person_list
            INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
            SET ' . implode(', ', $changes) . '
            WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id);

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_dao_mail = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_db);
            if (($l_mail_id = $l_dao_mail->mail_address_exists($l_old_data['isys_cats_person_list__isys_obj__id'], $p_mail))) {
                $l_dao_mail->set_primary_mail($l_old_data['isys_cats_person_list__isys_obj__id'], $l_mail_id);
            } else {
                if ($p_mail != '') {
                    if ($l_old_data['isys_cats_person_list__mail_address'] == '') {
                        $l_dao_mail->create($l_old_data['isys_cats_person_list__isys_obj__id'], C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    } else {
                        $l_dao_mail->save($l_old_data['isys_catg_mail_addresses_list__id'], C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    }
                } else {
                    $l_dao_mail->delete_primary_mail($l_old_data['isys_cats_person_list__isys_obj__id']);
                }
            }

            /**
             * @var $l_dao_relation isys_cmdb_dao_category_g_relation
             */
            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);

            // @see  ID-6903  Don't alter the organisation reference, if no value was given.
            if ($organizationWasGiven) {
                if ($p_organization > 0) {
                    // Update relation
                    $l_dao_relation->handle_relation(
                        $p_id,
                        'isys_cats_person_list',
                        defined_or_default('C__RELATION_TYPE__ORGANIZATION'),
                        $l_old_data[$this->m_table . '__isys_catg_relation_list__id'],
                        $p_organization,
                        $l_old_data[$this->m_table . '__isys_obj__id']
                    );
                } elseif ($l_old_data[$this->m_table . '__isys_catg_relation_list__id'] > 0) {
                    // Remove relation
                    $l_dao_relation->delete_relation($l_old_data[$this->m_table . '__isys_catg_relation_list__id']);
                }
            }

            $l_dao_global = new isys_cmdb_dao_category_g_global($this->m_db);
            $l_dao_global->handle_template_status($l_old_data["isys_obj__status"], $l_old_data["isys_obj__id"]);

            return true;
        }

        return false;
    }

    /**
     * @param   integer $p_obj_id
     * @param   string  $p_username
     * @param   string  $p_firstname
     * @param   string  $p_lastname
     * @param   string  $p_mail
     * @param   string  $p_phoneOffice
     * @param   string  $p_phonePrivate
     * @param   string  $p_phoneCell
     * @param   string  $p_fax
     * @param   string  $p_department
     * @param   string  $p_organization
     * @param   string  $p_description
     * @param   null    $p_ldap_server
     * @param   string  $p_ldap_dn
     * @param   integer $p_status
     * @param   string  $p_personnel_number
     * @param   string  $p_academic_degree
     * @param   string  $p_function
     * @param   string  $p_service_designation
     * @param   string  $p_city
     * @param   string  $p_zip_code
     * @param   string  $p_street
     * @param   string  $p_pager
     * @param   string  $p_salutation
     * @param   string  $p_created_by
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     */
    public function create(
        $p_obj_id = null,
        $p_username,
        $p_firstname,
        $p_lastname,
        $p_mail,
        $p_phoneOffice,
        $p_phonePrivate,
        $p_phoneCell,
        $p_fax,
        $p_department = "",
        $p_organization = "",
        $p_description = "",
        $p_ldap_server = null,
        $p_ldap_dn = "",
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_personnel_number = '',
        $p_academic_degree = "",
        $p_function = "",
        $p_service_designation = "",
        $p_city = "",
        $p_zip_code = "",
        $p_street = "",
        $p_pager = '',
        $p_salutation = '',
        $p_created_by = null
    ) {
        $l_created = false;
        $l_entry_id = false;
        $l_orga_id = (int)$p_organization;

        try {
            if (!$p_obj_id) {
                $l_created = true;
                $p_obj_id = $this->insert_new_obj(
                    $this->get_objtype_id_by_const_string($this->m_default_person_object_type),
                    false,
                    $p_firstname . " " . $p_lastname,
                    null,
                    C__RECORD_STATUS__NORMAL,
                    null,
                    null,
                    null,
                    null,
                    $p_created_by,
                    null,
                    $p_created_by
                );
            }

            if ($this->get_data_by_object($p_obj_id)
                    ->num_rows() == 0) {
                $l_entry_id = $this->create_connector('isys_cats_person_list', $p_obj_id);
            }
        } catch (Exception $e) {
            throw $e;
        }

        if ($l_orga_id === 0) {
            $l_organization = trim($p_organization);
            if (!empty($l_organization) && defined('C__OBJTYPE__ORGANIZATION')) {
                $l_orga_id = $this->get_obj_id_by_title($l_organization, C__OBJTYPE__ORGANIZATION);
                if (!$l_orga_id) {
                    $l_orga = new isys_cmdb_dao_category_s_organization_master($this->m_db);
                    $l_orga_id = $this->insert_new_obj(C__OBJTYPE__ORGANIZATION, false, $l_organization, null, C__RECORD_STATUS__NORMAL);
                    $l_orga->create($l_orga_id, C__RECORD_STATUS__NORMAL, $l_organization, '', '', '', '', '', '', '', null, null, '');
                }
            }
        }

        $l_dao = new isys_cmdb_dao_connection($this->m_db);
        if ($l_created) {
            $organizationConnectionId = $l_dao->add_connection($l_orga_id);
        } else {
            $organizationConnectionId = $l_dao->retrieve_connection('isys_cats_person_list', $l_entry_id);

            if (!$organizationConnectionId) {
                $organizationConnectionId = $l_dao->attach_connection('isys_cats_person_list', $l_entry_id, null, 'isys_cats_person_list__isys_connection__id');
            }
        }

        $l_sql = "UPDATE isys_cats_person_list
			SET isys_cats_person_list__first_name = " . $this->convert_sql_text($p_firstname) . ",
			isys_cats_person_list__last_name = " . $this->convert_sql_text($p_lastname) . ",
			isys_cats_person_list__title = " . $this->convert_sql_text($p_username) . ",
			isys_cats_person_list__phone_company = " . $this->convert_sql_text($p_phoneOffice) . ",
			isys_cats_person_list__phone_mobile = " . $this->convert_sql_text($p_phoneCell) . ",
			isys_cats_person_list__phone_home = " . $this->convert_sql_text($p_phonePrivate) . ",
			isys_cats_person_list__fax = " . $this->convert_sql_text($p_fax) . ",
			isys_cats_person_list__personnel_number = " . $this->convert_sql_text($p_personnel_number) . ",
			isys_cats_person_list__department = " . $this->convert_sql_text($p_department) . ",
			isys_cats_person_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ",
			isys_cats_person_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_person_list__pager = " . $this->convert_sql_text($p_pager) . ",
			isys_cats_person_list__salutation = " . $this->convert_sql_text($p_academic_degree) . ",
			isys_cats_person_list__academic_degree = " . $this->convert_sql_text($p_academic_degree) . ",
			isys_cats_person_list__function = " . $this->convert_sql_text($p_function) . ",
			isys_cats_person_list__service_designation = " . $this->convert_sql_text($p_service_designation) . ",
			isys_cats_person_list__city = " . $this->convert_sql_text($p_city) . ",
			isys_cats_person_list__zip_code = " . $this->convert_sql_text($p_zip_code) . ",
			isys_cats_person_list__street = " . $this->convert_sql_text($p_street) . ", ";

        if ($organizationConnectionId) {
            $l_sql .= "isys_cats_person_list__isys_connection__id = " . $this->convert_sql_id($organizationConnectionId) . ",";
        }

        if (!empty($p_ldap_dn)) {
            $l_sql .= "isys_cats_person_list__ldap_dn = " . $this->convert_sql_text($p_ldap_dn) . ", ";

            isys_cmdb_dao_category_g_ldap_dn::instance($this->m_db)
                ->save_single_value($p_obj_id, [
                    'title'  => $p_ldap_dn,
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
        }

        if ($p_ldap_server > 0) {
            $l_sql .= "isys_cats_person_list__isys_ldap__id = " . $this->convert_sql_id($p_ldap_server) . ", ";
        }

        $l_sql .= "isys_cats_person_list__status = " . $this->convert_sql_int($p_status) . "
			WHERE isys_cats_person_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            {
                $l_last_id = $this->get_last_insert_id();

                $l_dao_mail = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_db);
                if (($l_mail_id = $l_dao_mail->mail_address_exists($p_obj_id, $p_mail))) {
                    $l_dao_mail->set_primary_mail($p_obj_id, $l_mail_id);
                } else {
                    if ($p_mail != '') {
                        $l_dao_mail->create($p_obj_id, C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    }
                }

                if ($l_orga_id > 0) {
                    $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                    $l_dao_relation->handle_relation($l_last_id, "isys_cats_person_list", defined_or_default('C__RELATION_TYPE__ORGANIZATION'), null, $l_orga_id, $p_obj_id);
                }
            }
        } else {
            return false;
        }

        return $p_obj_id;
    }

    /**
     * Save global category monitor element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_new_id
     *
     * @return  NULL
     */
    public function create_element($p_cat_level, &$p_new_id)
    {
        return null;
    }

    /**
     * Extracts a contact string like "g12,o1,p15" to a hierachically representable array with persons, organisations and groups.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     *
     * @param   string  $p_string
     * @param   boolean $p_rm_dupes
     *
     * @return  array
     */
    public function extract_contact_string($p_string, $p_rm_dupes = true)
    {
        $l_contacts = null;
        if (strstr($p_string, ",")) {
            $l_explode = explode(",", $p_string);

            foreach ($l_explode as $l_value) {
                // Match the string.
                if (preg_match("{(?P<key>G|O|P)(?P<value>[0-9]+)}i", $l_value, $l_register)) {
                    // Continue if dupe detected.
                    if (is_array($l_contacts) && (in_array($l_register["value"], $l_contacts[$l_register["key"]]) && $p_rm_dupes)) {
                        continue;
                    }

                    $l_contacts[$l_register["key"]][] = $l_register["value"];
                }
            }
        } else {
            if (preg_match("{(?P<key>G|O|P)(?P<value>[0-9]+)}i", $p_string, $l_register)) {
                $l_contacts[$l_register["key"]][] = $l_register["value"];
            }
        }

        return $l_contacts;
    }

    /**
     * Method for simply updating the person name.
     *
     * @param   integer $p_person_object_id
     * @param   string  $p_first_name
     * @param   string  $p_last_name
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_person_name($p_person_object_id, $p_first_name, $p_last_name)
    {
        $l_sql = 'UPDATE isys_cats_person_list
			SET isys_cats_person_list__first_name = ' . $this->convert_sql_text($p_first_name) . ',
			isys_cats_person_list__last_name = ' . $this->convert_sql_text($p_last_name) . '
			WHERE isys_cats_person_list__isys_obj__id = ' . $this->convert_sql_id($p_person_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    // Removed: validate_user_data() & isys_rs_system

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            $l_data = $this->get_data_by_object($p_object_id);
            if ($p_status === isys_import_handler_cmdb::C__CREATE && $l_data->num_rows() == 0) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_person_list', $p_object_id);
                $l_data = null;
            } else {
                $l_data = $l_data->__to_array();
                $p_category_data['data_id'] = $l_data['isys_cats_person_list__id'];
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $organization = null;

                if (isset($p_category_data['properties']['organization'][C__DATA__VALUE])) {
                    $organization = trim($p_category_data['properties']['organization'][C__DATA__VALUE]);
                }

                // @see  ID-6713  This can happen during duplication of an object, this contact is assigned to.
                if ($organization === '' && isset($p_category_data['properties']['organization_title'][C__DATA__VALUE])) {
                    $organization = trim($p_category_data['properties']['organization_title'][C__DATA__VALUE]);
                }

                // Handle organization assignment.
                // @see  API-177  Don't create and assign an empty organization.
                if ($organization !== null && $organization !== '') {
                    // Don't assign anything if object does not exist
                    if (is_numeric($organization)) {
                        if (!$this->obj_exists($organization)) {
                            $organization = null;
                        }
                    } else {
                        // Try to find organization by title.
                        $organization = isys_cmdb_dao_category_s_organization_master::instance($this->get_database_component())->get_object_id_by_title($organization, true);
                    }
                }

                // @See ID-4336: If first_name and last_name is empty than use the object title
                if ($l_data && isset($l_data['isys_obj__title']) && empty($p_category_data['properties']['first_name'][C__DATA__VALUE]) && empty($p_category_data['properties']['last_name'][C__DATA__VALUE])) {
                    $p_category_data['properties']['first_name'][C__DATA__VALUE] = $l_data['isys_obj__title'];

                    if (strpos($l_data['isys_obj__title'], ' ') !== false) {
                        $p_category_data['properties']['first_name'][C__DATA__VALUE] = substr($l_data['isys_obj__title'], 0, strpos($l_data['isys_obj__title'], ' '));
                        $p_category_data['properties']['last_name'][C__DATA__VALUE] = substr($l_data['isys_obj__title'], strpos($l_data['isys_obj__title'], ' ') + 1);
                    }
                }

                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['first_name'][C__DATA__VALUE],
                    $p_category_data['properties']['last_name'][C__DATA__VALUE],
                    $p_category_data['properties']['mail'][C__DATA__VALUE],
                    $p_category_data['properties']['phone_company'][C__DATA__VALUE],
                    $p_category_data['properties']['phone_home'][C__DATA__VALUE],
                    $p_category_data['properties']['phone_mobile'][C__DATA__VALUE],
                    $p_category_data['properties']['fax'][C__DATA__VALUE],
                    $p_category_data['properties']['department'][C__DATA__VALUE],
                    $organization,
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['ldap_id'][C__DATA__VALUE],
                    $p_category_data['properties']['ldap_dn'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['personnel_number'][C__DATA__VALUE],
                    $p_category_data['properties']['academic_degree'][C__DATA__VALUE],
                    $p_category_data['properties']['function'][C__DATA__VALUE],
                    $p_category_data['properties']['service_designation'][C__DATA__VALUE],
                    $p_category_data['properties']['city'][C__DATA__VALUE],
                    $p_category_data['properties']['zip_code'][C__DATA__VALUE],
                    $p_category_data['properties']['street'][C__DATA__VALUE],
                    $p_category_data['properties']['pager'][C__DATA__VALUE],
                    $p_category_data['properties']['salutation'][C__DATA__VALUE]
                );

                $this->set_source_table('isys_cats_person_list');

                // @see  ID-6713  Only write the custom properties if at least one is set.
                $customKeys = array_filter($p_category_data['properties'], function ($key) {
                    return strpos($key, 'custom_') === 0;
                }, ARRAY_FILTER_USE_KEY);

                if (!empty($customKeys)) {
                    // Save custom properties
                    $this->save_custom_properties($p_category_data['data_id'], [
                        'C__CONTACT__CUSTOM1' => $p_category_data['properties']['custom_1']['value'],
                        'C__CONTACT__CUSTOM2' => $p_category_data['properties']['custom_2']['value'],
                        'C__CONTACT__CUSTOM3' => $p_category_data['properties']['custom_3']['value'],
                        'C__CONTACT__CUSTOM4' => $p_category_data['properties']['custom_4']['value'],
                        'C__CONTACT__CUSTOM5' => $p_category_data['properties']['custom_5']['value'],
                        'C__CONTACT__CUSTOM6' => $p_category_data['properties']['custom_6']['value'],
                        'C__CONTACT__CUSTOM7' => $p_category_data['properties']['custom_7']['value'],
                        'C__CONTACT__CUSTOM8' => $p_category_data['properties']['custom_8']['value']
                    ]);
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
