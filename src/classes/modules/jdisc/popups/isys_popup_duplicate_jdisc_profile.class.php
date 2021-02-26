<?php

/**
 * i-doit
 *
 * Popup for JDisc profile duplication
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_duplicate_jdisc_profile extends isys_component_popup
{
    /**
     * Instance of module DAO.
     *
     * @var  isys_jdisc_dao
     */
    protected $m_dao;

    /**
     * Instance of logger.
     *
     * @var  isys_log
     */
    protected $m_log;

    protected $m_module = 'jdisc';

    protected $m_type = 'profile';

    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * Handles module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Prepare template for popup:
        $l_tplpopup = isys_component_template::instance();
        $l_tplpopup->assign('file_body', 'popup/duplicate_jdisc_profile.tpl');
        $l_tplpopup->activate_editmode();

        try {
            $l_posts = $p_modreq->get_posts();
            $l_ids = [];
            if (!isset($l_posts['id']) || !is_array($l_posts['id']) || count($l_posts['id']) === 0) {
                throw new Exception(isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__JDISC__POPUP__ERROR__NO_SELECTED_PROFILE'));
            }

            $l_string_to_int = function ($p_string) {
                return (int)$p_string;
            };
            $l_ids = array_map($l_string_to_int, $l_posts['id']);

            $l_selections = [
                'id',
                'title'
            ];
            $l_all_profiles = $this->m_dao->get_profiles($l_selections);
            $l_profiles = [];

            foreach ($l_all_profiles as $l_profile) {
                if (in_array($l_profile['id'], $l_ids)) {
                    $l_profiles[] = [
                        'id'    => 'C__PROFILE__' . $l_profile['id'],
                        'title' => $l_profile['title']
                    ];
                }
            }

            if (count($l_profiles) === 0) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('No profile found.'));
            }

            return $l_tplpopup->assign('profiles', $l_profiles);
        } catch (Exception $e) {
            return $l_tplpopup->assign('error', $e->getMessage());
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_log = isys_factory_log::get_instance($this->m_module);
        $this->m_dao = new isys_jdisc_dao($this->database, $this->m_log);
    }
}
