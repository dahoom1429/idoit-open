<?php

/**
 * i-doit
 *
 * Smarty plugin for password input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_password extends isys_smarty_plugin_f_text
{
    const PASSWORD_CHANGED   = 'changed';
    const PASSWORD_SET_EMPTY = 'set-empty';
    const PASSWORD_UNCHANGED = 'unchanged';

    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return [];
    }

    /**
     * Display in view mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        if (!isset($p_params['maskPassword']) || $p_params['maskPassword']) {
            $p_params['p_bPassword'] = true;
        }
        $p_params['p_strValue'] = isys_glob_htmlentities($p_params['p_strValue']);

        return parent::navigation_view($p_tplclass, $p_params);
    }

    /**
     * Display in edit mode.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_dirs;

        if (empty($p_params['id'])) {
            $p_params['id'] = $p_params['name'];
        }

        if (strpos($p_params['name'], '[') !== false) {
            $l_hiddenName = substr($p_params['name'], 0, strpos($p_params['name'], '[')) . '__action' . substr($p_params['name'], strpos($p_params['name'], '['));
        } else {
            $l_hiddenName = $p_params['name'] . '__action';
        }

        if (strpos($p_params['id'], '[') !== false) {
            $l_hiddenId = substr($p_params['id'], 0, strpos($p_params['id'], '[')) . '__action' . substr($p_params['id'], strpos($p_params['id'], '['));
        } else {
            $l_hiddenId = $p_params['id'] . '__action';
        }

        //$p_params['p_bPasswordHideValue'] = true;
        $p_params['p_strPlaceholder'] = 'LC__UNIVERSAL__PASSWORD';
        if (!isset($p_params['maskPassword']) || $p_params['maskPassword']) {
            $p_params['inputType'] = 'password';
            $p_params['p_strValue'] = str_repeat('*', strlen($p_params['p_strValue']));

            // Append the "focus" and "blur" event, that empties the value to enforce the user to input the complete password.
            $p_params['p_onFocus'] .= "if(\$F('" . $l_hiddenId . "') == '" . self::PASSWORD_UNCHANGED . "') { this.setValue(''); }";
            $p_params['p_onBlur'] .= "if(\$F('" . $l_hiddenId . "') == '" . self::PASSWORD_UNCHANGED . "' && this.getValue().blank()) { this.setValue('" .
                $p_params['p_strValue'] . "'); }";
        }

        // Append the "password change" event.
        $p_params['p_onChange'] .= "$('" . $l_hiddenId . "').setValue('" . self::PASSWORD_CHANGED . "');";

        $l_set_empty_addon = '<div class="input-group-addon input-group-addon-clickable" title="' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__SET_EMPTY') . '">' . '<img src="' . $g_dirs['images'] . 'icons/silk/cross.png" onclick="' . "$('" . $p_params['id'] .
            "').setValue(''); $('" . $l_hiddenId . "').setValue('" . self::PASSWORD_SET_EMPTY . "');" . '" />' . '</div>';

        $l_lock_addon = '<div class="input-group-addon" title="' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__PASSWORD_FIELD') . '">' . '<img src="' . $g_dirs['images'] . 'icons/silk/lock.png" />' . '</div>';

        // Add the "set empty" and "lock" addons at the end.
        $this->addInputGroupAddonAfter($l_set_empty_addon)
            ->addInputGroupAddonAfter($l_lock_addon);

        return parent::navigation_edit($p_tplclass, $p_params) . '<input type="hidden" name="' . $l_hiddenName . '" id="' . $l_hiddenId . '" value="' .
            self::PASSWORD_UNCHANGED . '" />';
    }
}
