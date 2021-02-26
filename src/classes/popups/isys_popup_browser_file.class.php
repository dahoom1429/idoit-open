<?php

/**
 * i-doit
 *
 * File browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_file extends isys_component_popup
{
    /**
     * @desc Minimum right to create an object
     * Example: isys_auth::EDIT
     */
    const C__CHECK_RIGHT = 'checkRight';

    /**
     * This variable will hold all file infos. Used for the ajax request.
     *
     * @var  array
     */
    protected $m_file_infos = [];

    /**
     * Should be file browser use the auth-system?
     *
     * @var boolean
     */
    protected $useAuth = false;

    /**
     * Instance of the CMDB auth class.
     *
     * @var isys_auth_cmdb
     */
    protected $auth;

    /**
     * This variable will hold the parameters. Used for the ajax request.
     *
     * @var  array
     */
    protected $m_params = [];

    /**
     * isys_popup_browser_file constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->useAuth = (bool) isys_tenantsettings::get('auth.use-in-file-browser', false);
        $this->auth = isys_auth_cmdb::instance();
    }

    /**
     * Handles SMARTY request for location browser.
     *
     * @param isys_component_template $template
     * @param                         $p_params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template $template, $p_params)
    {
        $l_id = $p_params['p_strValue'];

        if (is_numeric($p_params['p_strSelectedID'])) {
            $l_id = $p_params['p_strSelectedID'];
        }

        if (strpos($p_params['name'], '[') !== false && strpos($p_params['name'], ']') !== false) {
            $l_tmp = explode('[', $p_params['name']);
            $l_view_name = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden_name = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $l_view_name = $p_params['name'] . '__VIEW';
            $l_hidden_name = $p_params['name'] . '__HIDDEN';
        }

        $l_plugin = new isys_smarty_plugin_f_text;
        $l_strHiddenField = '<input id="' . $l_hidden_name . '" name="' . $l_hidden_name . '" type="hidden" value="' . $l_id . '" />';

        if (!isset($this->m_params[self::C__CHECK_RIGHT])) {
            $p_params[self::C__CHECK_RIGHT] = 'isys_auth::EDIT';
        }

        $l_hide_controls = (isset($p_params['p_bReadonly']) && $p_params['p_bReadonly']);

        // Set parameters for the f_text plug-in.
        $p_params['return_name'] = $p_params['name'];
        $p_params['name'] = $l_view_name;
        $p_params['p_strSelectedID'] = $l_id;
        $p_params['p_strValue'] = $this->format_selection($l_id);
        $p_params['p_additional'] .= ' data-hidden-field="' . str_replace('"', "'", $l_hidden_name) . '" data-last-value="' . $p_params['p_strValue'] . '"';
        $p_params['p_onClick'] = "if (!this.getValue().blank()) {this.writeAttribute('placeholder',this.readAttribute('data-last-value')).setValue('');}";
        $p_params['p_onBlur'] = "if (this.getValue().blank()) {this.setValue(this.readAttribute('data-last-value'));}";
        $p_params['p_strSuggest'] = "object";
        $p_params['p_strSuggestView'] = $l_view_name;
        $p_params['p_strSuggestHidden'] = $l_hidden_name;
        $p_params['p_strSuggestParameters'] = "parameters: { " . isys_popup_browser_object_ng::C__CAT_FILTER .
            ": 'C__CATS__FILE'}, selectCallback: function(view, li) {view.writeAttribute('data-last-value', li.readAttribute('title'));}";

        if (isys_glob_is_edit_mode() || $p_params[isys_popup_browser_object_ng::C__EDIT_MODE]) {
            // This is necessary to prevent multiple nested input-groups.
            $p_params['disableInputGroup'] = true;

            $l_return = $l_plugin->navigation_edit($template, $p_params);

            if (!$l_hide_controls) {
                $l_return .= '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__CHOOSE') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $this->process_overlay('', 950, 570, $p_params, 'popup_commentary') . ';" >
					    <img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="' . $this->language->get('LC__UNIVERSAL__CHOOSE') . '" />
					</a><a href="javascript:" title="' . $this->language->get("LC__UNIVERSAL__DETACH") . '" class="input-group-addon input-group-addon-clickable" onClick="$(\'' . $l_view_name . '\').setValue(\'' . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . '\');$(\'' . $l_hidden_name . '\').setValue(0);" >
					    <img src="' . isys_application::instance()->www_path . 'images/icons/silk/detach.png" alt="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" />
					</a>';
            }

            return $l_return . $l_strHiddenField;
        }

        $p_params['p_bHtmlDecode'] = true;

        return $l_plugin->navigation_view($template, $p_params) . $l_strHiddenField;
    }

    /**
     * Displays the formatted filename-string.
     *
     * @param  integer $p_objid
     * @param  boolean $plain
     *
     * @return string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_objid, $plain = false)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();

        // We need a DAO for the object name.
        $l_dao_cmdb = new isys_cmdb_dao($this->database);

        if ($p_objid > 0) {
            $l_obj = $l_dao_cmdb->get_object_by_id($p_objid)->get_row();

            if ($this->useAuth && !$this->auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_objid)) {
                return '[' . $this->language->get('LC__UNIVERSAL__HIDDEN') . ']';
            }

            if (isys_glob_is_edit_mode()) {
                return $this->language->get($l_obj['isys_obj_type__title']) . ' » ' . $l_obj['isys_obj__title'];
            }

            return $this->language->get($l_obj['isys_obj_type__title']) . ' &raquo; ' . $l_quick_info->get_quick_info($p_objid, $l_obj['isys_obj__title'], C__LINK__OBJECT);
        }

        return $this->language->get('LC__CMDB__BROWSER_OBJECT__NONE_SELECTED');
    }

    /**
     * Method for loading the popup template and assigning stuff.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $this->m_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        $l_tree = $this->build_tree(isys_component_tree::factory('file_browser_filetree'));

        $l_allowed_filesize = isys_convert::memory(isys_helper_upload::get_max_upload_size(), 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD);

        $this->template->activate_editmode()
            ->assign('callback_accept', $this->m_params['callback_accept'] ?: '')
            ->assign('return_name', $this->m_params['return_name'])
            ->assign('browser', $l_tree->process())
            ->assign('file_infos', $this->m_file_infos)
            ->assign('new_file_description', $this->language->get('LC_FILEBROWSER__NEW_FILE_DESCRIPTION', [$l_allowed_filesize]))
            ->assign('upload_rights', isys_auth_cmdb::instance()->is_allowed_to(isys_auth::EDIT, 'OBJ_IN_TYPE/C__OBJTYPE__FILE'))
            ->display('popup/filebrowser.tpl');
        die;
    }

    /**
     * Returns the file infos, collected by the "build_tree" method.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_file_infos()
    {
        return $this->m_file_infos;
    }

    /**
     * Outsourced method for building the file tree. Will be used from the "handle_module_request" method, but also by the ajax handler "isys_ajax_handler_file.
     *
     * @param  isys_component_tree $p_tree
     *
     * @return isys_component_tree
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function build_tree($p_tree)
    {
        global $g_dirs;

        $hidden = '[' . $this->language->get('LC__UNIVERSAL__HIDDEN') . ']';

        $l_cmdb_dao = isys_cmdb_dao::instance($this->database);
        $l_file_dao = isys_cmdb_dao_file::instance($this->database);

        $l_file_categories = isys_factory_cmdb_dialog_dao::get_instance($this->database, 'isys_file_category')
            ->get_data();

        // Create root node.
        $l_root_node = $p_tree->add_node(0, -1, '<span class="ml5">' . $this->language->get('LC_UNIVERSAL__FILE_BROWSER') . '</span>', '', '', isys_application::instance()->www_path . 'images/icons/silk/database.png');

        if (is_array($l_file_categories) && count($l_file_categories) > 0) {
            foreach ($l_file_categories as $l_id => $l_row) {
                // Here we create the "category" folders.
                $p_tree->add_node($l_id, $l_root_node, '<span>' . $l_row['isys_file_category__title'] . '</span>', '', '', isys_application::instance()->www_path . 'images/dtree/folder.gif');
            }
        }

        $filesResult = $l_file_dao->get_active_file_versions();
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        while ($fileRow = $filesResult->get_row()) {
            // At first we calculate the filesize.
            $l_filesize = 0;
            $authAllowed = true;
            $l_file_path = $fileRow['isys_file_physical__filename'] ? isys_application::instance()->getUploadFilePath($fileRow['isys_file_physical__filename']) : '';
            $selectedFile = ($fileRow['isys_file_version__isys_obj__id'] == $this->m_params['p_strSelectedID']);

            if ($l_file_path && file_exists($l_file_path)) {
                $l_filesize = filesize($l_file_path);
            }

            if ($l_filesize <= 0) {
                $l_filesize = $l_empty_value;
            } else {
                $l_filesize = isys_helper::filter_number(isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD));
            }

            // Secondly, we get the user who uploaded the file.
            $l_person = $l_cmdb_dao->get_obj_name_by_id_as_string($fileRow['isys_file_physical__user_id_uploaded']);

            // Assign intern variables.
            $this->m_file_infos[$fileRow['isys_file_version__id']] = [
                'id'           => $fileRow['isys_file_version__id'],
                'obj_id'       => $fileRow['isys_file_version__isys_obj__id'],
                'filename'     => isys_glob_htmlentities($fileRow['isys_file_physical__filename_original']),
                'fileobj_name' => isys_glob_htmlentities($l_cmdb_dao->get_obj_name_by_id_as_string($fileRow['isys_file_version__isys_obj__id'])),
                'filesize'     => $l_filesize . ' MB',
                'uploaded_by'  => $l_person,
                'created_at'   => $fileRow['isys_date_up'],
                'fileversion'  => isys_glob_htmlentities($fileRow['isys_file_version__title']),
                'filerevision' => $fileRow['isys_file_version__revision'],
                'category'     => $fileRow['isys_file_category__title']
            ];

            if ($this->useAuth && !$this->auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileRow['isys_file_version__isys_obj__id'])) {
                $authAllowed = false;
                $allowedToSeePerson = $this->auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileRow['isys_file_physical__user_id_uploaded']);

                // @see  ID-5510  Hide infos, if the current file is not the selected one.
                if (!$selectedFile) {
                    $this->m_file_infos[$fileRow['isys_file_version__id']]['filename'] = $hidden;
                    $this->m_file_infos[$fileRow['isys_file_version__id']]['fileobj_name'] = $hidden;
                    $this->m_file_infos[$fileRow['isys_file_version__id']]['fileversion'] = $hidden;
                    $this->m_file_infos[$fileRow['isys_file_version__id']]['filerevision'] = $hidden;
                }

                $this->m_file_infos[$fileRow['isys_file_version__id']]['uploaded_by'] = $allowedToSeePerson ? $l_person : $hidden;
            }

            $fileName = $fileRow['isys_file_physical__filename_original'];
            $l_strObjectName = $fileRow['isys_obj__title'];

            $l_parent_node_id = $fileRow['isys_file_category__id'];

            if ($l_parent_node_id === null) {
                $l_parent_node_id = $l_root_node;
            }

            // Change file name
            if (strlen($l_strObjectName) > 0) {
                $fileName = $l_strObjectName . ' - "' . $fileName . '"';
            } else {
                $fileName = '"' . $fileName . '"';
            }

            if (!$selectedFile && !$authAllowed) {
                $fileName = $hidden;
            }

            // Add the next file to tree.
            $p_tree->add_node(
                10000 + $fileRow['isys_file_version__id'],
                $l_parent_node_id,
                '<span class="file-object mouse-pointer ' . ($selectedFile ? 'text-bold' : '') . '" data-file-version-id="' . $fileRow['isys_file_version__id'] . '">' .
                $fileName . '</span>',
                '',
                '',
                '',
                $selectedFile,
                '',
                '',
                ($selectedFile ? true : $authAllowed)
            );
        }

        return $p_tree;
    }
}
