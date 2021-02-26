<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_combobox extends isys_ajax_handler
{
    /**
     * Init method for this AJAX request.
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json; charset=UTF-8');

        $l_data = [];

        switch ($_GET['func']) {
            case 'load':
                $l_data = $this->load($_POST['table'], $_POST['parent_table'], $_POST['parent_table_id'], $_POST['condition']);
                break;

            case 'load_sub':
                if (isset($_POST['p_id'])) {
                    $l_id = $_POST['p_id'];

                    if (($l_id > 0) && preg_match('/\w/i', $_POST['p_table']) && preg_match('/\w/i', $_POST['p_child_table'])) {
                        $l_data = $this->load_sub($l_id, $_POST['p_table'], $_POST['p_child_table']);
                    }
                }
                break;

            case 'load_extended':
                $l_data = $this->load_extended($_POST['table'], $_POST['parent_table'], $_POST['parent_table_id'], $_POST['condition']);
                break;

            case 'save_cat_data':
                $l_data = $this->save_cat_data($_POST['cat_table_object'], $_POST['table'], isys_format_json::decode($_POST['data']));
                break;

            case 'save':
                $l_data = $this->save(isys_format_json::decode($_POST['data']), $_POST['table'], isys_format_json::decode($_POST['parent']), $_POST['condition']);

                // Add relation type to contact role
                if ($l_data > 0 && $_POST['table'] == 'isys_contact_tag') {
                    $this->save_field('isys_contact_tag', $l_data, defined_or_default('C__RELATION_TYPE__USER'), 'isys_relation_type__id');
                }

                break;

            case 'save_field':
                $l_data = $this->save_field($_POST['table'], $_POST['id'], $_POST['title']);
                break;

            case 'save_relation_type':
                $l_data = $this->save_relation_type($_POST['relation_type__title'], $_POST['relation_type__master'], $_POST['relation_type__slave']);
                break;
        }

        // Echo our return values as JSON encoded string.
        echo isys_format_json::encode($l_data);

        // And die, since this is a ajax request.
        $this->_die();
    }

    /**
     * Method for retrieving the data from a dialog box.
     *
     * @param   string  $p_table
     * @param   string  $p_parenttable
     * @param   integer $p_parenttable_id
     * @param   string  $p_condition
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function load($p_table, $p_parenttable = '', $p_parenttable_id = 0, $p_condition = null)
    {
        $l_data = [];
        foreach ($this->load_extended($p_table, $p_parenttable, $p_parenttable_id, $p_condition) as $l_id => $l_row) {
            $l_data[$l_id] = $l_row['title'];
        }

        return $l_data;
    }

    /**
     * Method for retrieving the data from a dialog box.
     *
     * @param   string  $p_table
     * @param   string  $p_parenttable
     * @param   integer $p_parenttable_id
     * @param   string  $p_condition
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function load_extended($p_table, $p_parenttable = '', $p_parenttable_id = 0, $p_condition = null)
    {
        $l_dao = new isys_cmdb_dao($this->m_database_component);
        $l_data = $l_tmp = [];

        $l_sql = 'SELECT * ' . 'FROM ' . $p_table . ' ' . ((!empty($p_condition)) ? 'WHERE ' . $p_condition . ' ' : '') . ' ORDER BY \'' . $p_table . '__title ASC\';';

        if ($p_parenttable_id > 0 && !empty($p_parenttable)) {
            $l_sql = 'SELECT * ' . 'FROM ' . $p_table . ' ' . 'WHERE ' . $p_table . '__' . $p_parenttable . '__id = ' . $l_dao->convert_sql_int($p_parenttable_id) .
                ' ORDER BY \'' . $p_table . '__title ASC\';';
        }

        $l_res = $l_dao->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_title = isys_application::instance()->container->get('language')
                ->get(trim($l_row[$p_table . '__title']));
            $l_data[$l_title . ' ' . $l_row[$p_table . '__id']] = [
                'id'          => $l_row[$p_table . '__id'],
                'title'       => $l_title,
                'title_const' => trim($l_row[$p_table . '__title']),
                'constant'    => trim($l_row[$p_table . '__const'])
            ];
        }

        // We need the translated title as key to sort it alphabetically.
        uksort($l_data, 'strcasecmp');

        $l_return = [];
        foreach ($l_data as $l_item) {
            /**
             * @desc  adding .' ' so that javascript does not interprete the key as an integer, which results in a non-associative representation and auto sorting by key-number..
             * @fixed DS
             */
            $l_return[$l_item['id'] . ' '] = $l_item;
        }

        return $l_return;
    }

    /**
     * Method for retrieving the data from a sub-dialog box.
     *
     * @param   integer $p_id
     * @param   string  $p_table
     * @param   string  $p_child_table
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function load_sub($p_id, $p_table, $p_child_table)
    {
        $l_dao = new isys_cmdb_dao($this->m_database_component);

        $whereCase = '';
        if ($p_id && ($p_id != (-1))) {
            $whereCase = ' WHERE ' . $p_child_table . '__' . $p_table . '__id = ' . $p_id;
        }

        $l_sql = 'SELECT ' . $p_child_table . '__id AS id, ' . $p_child_table . '__title AS title ' . 'FROM ' . $p_child_table .
            $whereCase;

        $l_res = $l_dao->retrieve($l_sql);

        $l_data = [];
        while ($l_row = $l_res->get_row()) {
            $l_data[] = [
                'id'    => $l_row['id'],
                'title' => $l_row['title']
            ];
        }

        isys_glob_sort_array_by_column($l_data, 'title');

        return $l_data;
    }

    /**
     * Method for saving items and positions to a dialog+ table.
     *
     * @param   array  $l_data
     * @param   string $p_table
     * @param   array  $p_parent
     * @param   string $p_condition
     *
     * @return  int ID of the selected entry
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save(array $l_data, $p_table, $p_parent = [], $p_condition = null)
    {
        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance($this->m_database_component);
        $l_last_id = null;
        $l_sql = '';
        $l_existing_values = [];

        foreach ($l_data as $i => $l_dataset) {
            // New entries have "-" as ID.
            if ($l_dataset['id'] == '-') {
                if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
                    $l_dataset['name'] = isys_helper::sanitize_text($l_dataset['name']);
                }

                // Check if its already exists
                foreach ($l_data as $l_existing_data) {
                    if ($l_existing_data['id'] != '-') {
                        if ($l_existing_data['name'] == $l_dataset['name']) {
                            $l_existing_values[] = $l_existing_data['name'];
                            continue 2;
                        }
                    }
                }

                $l_sql = 'INSERT INTO ' . $p_table . ' SET ' . $p_table . '__title = ' . $l_dao->convert_sql_text($l_dataset['name']) . ', ' . $p_table . '__status = ' .
                    $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ' . ((!empty($p_condition)) ? ',' . $p_condition : ';');

                // When we have a dependency, we have to write another SQL query.
                if ($p_parent['selected_id'] > 0 && !empty($p_parent['table'])) {
                    $l_sql = 'INSERT INTO ' . $p_table . ' (' . $p_table . '__title, ' . $p_table . '__status, ' . $p_table . '__' . $p_parent['table'] . '__id' .
                        ') VALUES (' . $l_dao->convert_sql_text($l_dataset['name']) . ', ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ', ' .
                        $l_dao->convert_sql_int($p_parent['selected_id']) . ');';
                }

                // Write
                $l_dao->update($l_sql);

                // is entry selected?
                if ($l_dataset['checked'] === true) {
                    $l_last_id = $l_dao->get_last_insert_id();
                }
            } elseif ($l_dataset['checked'] == '1') {
                // This is an existing value
                $l_last_id = $l_dataset['id'];
            }
        }

        // Apply update if needed
        if ($l_sql !== '') {
            // Commit
            $l_dao->apply_update();
        }

        if (count($l_existing_values)) {
            isys_notify::warning(isys_application::instance()->container->get('language')
                ->get('LC__POPUP__DIALOG_PLUS__MESSAGE__DUPLICATE_ENTRY', ['<ul><li>' . implode('</li><li>', $l_existing_values) . '</li></ul>']), ['sticky' => true]);
        }

        return (int)$l_last_id;
    }

    /**
     * Method for saving category data by a dialog+ popup.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_table
     * @param   array   $p_data
     *
     * @return  integer
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function save_cat_data($p_obj_id, $p_table, array $p_data)
    {
        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance($this->m_database_component);
        $l_last_id = null;

        if (!$this->m_database_component->is_field_existent($p_table, $p_table . '__isys_obj__id')) {
            throw new isys_exception_dao('Attention! The field "' . $p_table . '__isys_obj__id" does not exist in table "' . $p_table . '"."');
        }

        foreach ($p_data as $l_dataset) {
            // New entries have "-" as ID.
            if ($l_dataset['id'] == '-') {
                if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
                    $l_dataset['name'] = isys_helper::sanitize_text($l_dataset['name']);
                }

                $l_sql = 'INSERT INTO ' . $p_table . ' SET ' . $p_table . '__title = ' . $l_dao->convert_sql_text($l_dataset['name']) . ', ' . $p_table . '__isys_obj__id = ' .
                    $l_dao->convert_sql_id($p_obj_id) . ', ' . $p_table . '__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

                // Write.
                $l_dao->update($l_sql);

                // Is entry selected?
                if ($l_dataset['checked'] === true) {
                    $l_last_id = $l_dao->get_last_insert_id();
                }
            } elseif ($l_dataset['checked'] == '1') {
                // This is an existing value.
                $l_last_id = $l_dataset['id'];
            }
        }
        $l_dao->apply_update();

        return (int)$l_last_id;
    }

    /**
     * Method for saving a single new title to a dialog-entry.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_field
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function save_field($p_table, $p_id, $p_title, $p_field = 'title')
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database_component);

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
            $p_title = isys_helper::sanitize_text($p_title);
        }

        if (is_numeric($p_title) && (strpos($p_title, '.') === false && strpos($p_title, ',') === false && strpos($p_title, '0') !== 0)) {
            $l_value = $l_dao->convert_sql_id($p_title);
        } else {
            $l_value = $l_dao->convert_sql_text($p_title);
        }

        $l_sql = 'UPDATE ' . $p_table . ' SET ' . $p_table . '__' . $p_field . ' = ' . $l_value . ' WHERE ' . $p_table . '__id = ' . $l_dao->convert_sql_id($p_id) . ';';

        try {
            if ($l_dao->update($l_sql)) {
                return [
                    'success' => true,
                    'message' => ''
                ];
            }

            $l_message = '?';
        } catch (Exception $e) {
            $l_message = $e->getMessage();
        }

        return [
            'success' => false,
            'message' => $l_message
        ];
    }

    /**
     * Method for saving the "relation-type" browser (only used in relation category).
     *
     * @param   string $p_title
     * @param   string $p_master
     * @param   string $p_slave
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function save_relation_type($p_title, $p_master, $p_slave)
    {
        $l_return = [];

        try {
            $l_popup_relation = new isys_popup_relation_type();

            if ($l_id = $l_popup_relation->create($p_title, $p_master, $p_slave)) {
                $l_return = [
                    'success' => true,
                    'id'      => $l_id
                ];

                $l_dialog_obj = new isys_smarty_plugin_f_dialog();
                $l_return['items'] = $l_dialog_obj->get_array_data("isys_relation_type", C__RECORD_STATUS__NORMAL, null, "isys_relation_type__type = '2'");
            }
        } catch (Exception $e) {
            $l_return = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $l_return;
    }
}
