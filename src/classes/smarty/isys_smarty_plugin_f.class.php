<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * The plugin class name.
     *
     * @var  string
     */
    protected $m_strPluginClass = "";

    /**
     * The name of the current plugin.
     *
     * @var  string
     */
    protected $m_strPluginName = "";

    /**
     * Defines, if the current plugin is in edit mode.
     *
     * @var  boolean
     */
    protected $m_bEditMode = false;

    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @var  boolean
     */
    protected $m_enableMetaMap = true;

    /**
     * Parameter array.
     *
     * @var  array
     */
    protected $m_parameter = [];

    /**
     * Input groups to add before / after the field.
     *
     * @var array
     */
    private $inputGroupAddons = ['before' => [], 'after' => []];

    /**
     * Method for setting a single or multiple parameters.
     *
     * @param   mixed $p_key
     * @param   mixed $p_value
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_parameter($p_key, $p_value = null)
    {
        if (is_array($p_key) && $p_value === null) {
            $this->m_parameter = $p_key;
        } else {
            if (is_scalar($p_key)) {
                $this->m_parameter[$p_key] = $p_value;
            }
        }

        return $this;
    }

    /**
     * Method for getting a single or all parameters.
     *
     * @param   string $p_key
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_parameter($p_key = null)
    {
        if ($p_key === null) {
            return $this->m_parameter;
        }

        return $this->m_parameter[$p_key];
    }

    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return $this->m_enableMetaMap;
    }

    /**
     * Returns map for the Smarty Meta Map (SM2).
     *
     * @static
     * @return  array
     */
    public static function get_meta_map()
    {
        return [];
    }

    /**
     * Wrapper for the navigation_view.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        $this->m_bEditMode = true;

        return '';
    }

    /**
     * Method for navigation-view.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        return '';
    }

    /**
     * Attach wiki if configured.
     *
     * @param   array $p_params
     *
     * @return  string
     */
    protected function attach_wiki($p_params)
    {
        global $g_dirs;

        if ($this->m_strPluginName && !$p_params['p_bInvisible']) {
            $l_wiki_url = trim(isys_tenantsettings::get('gui.wiki-url'));

            if (!empty($l_wiki_url) && empty($p_params["nowiki"]) && is_null($p_params["p_bDisabled"])) {
                // ID-2865
                // $l_last_char = substr($l_wiki_url, -1);
                //
                // if ($l_last_char !== '/' && $l_last_char !== ':')
                // {
                //     $l_wiki_url .= '/';
                // } // if

                return ' <a target="_blank" href="' . $l_wiki_url . $this->m_strPluginName . '" class="input-group-addon input-group-addon-clickable wiki-link" title="' .
                    isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__WIKI') . '"><img src="' . $g_dirs["images"] . 'icons/silk/world_link.png" /></a>';
            }
        }

        return '';
    }

    /**
     * Get html string for the InfoIcon.
     *
     * @param   array $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function getInfoIcon($p_params)
    {
        global $g_dirs;

        $l_strInfoIconSource = "";
        $l_strTitle = "";
        $l_strFootnote = "";
        $l_bInfoIcon = false;

        if (isset($p_params["p_bInfoIconSpacer"]) && (!$p_params["p_bInfoIconSpacer"] || $p_params["p_bInfoIconSpacer"] == "0")) {
            return '';
        }

        // If p_strHelp, p_strInfo or p_strError are set, overwrite the title and show the InfoIcon (if p_bInfoIcon is not set to 0).
        if (!empty($p_params["p_strInfoIconError"])) {
            $l_strTitle = $p_params["p_strInfoIconError"];
            $l_strInfoIconSource = $g_dirs["images"] . "icons/alert-icon.png";
            $l_bInfoIcon = true;
        }

        // @todo  Remove "p_strFootnote" somehow, this parameter is only used at two places in the administration.
        if (!empty($p_params['p_strFootnote']) && ($p_params["p_bInfoIconSpacer"] != "0" || $l_bInfoIcon)) {
            $l_strFootnote = '<span style="position:absolute;float:left;">' . $p_params['p_strFootnote'] . '</span>';
        }

        // @todo  The "p_strInfoIconClass" parameter is only used once (in "isys_smarty_plugin_f_wysiwyg"). Try to replace this somehow.
        if ($p_params["p_bInfoIcon"] != "0" && $l_bInfoIcon == true) {
            // Show InfoIcon.
            $l_return = $l_strFootnote . '<img class="infoIcon ' . $p_params["p_strInfoIconClass"] . ' mr5" src="' . $l_strInfoIconSource . '" alt="' . $l_strTitle .
                '" title="' . $l_strTitle . '" height="11px" width="15px" />';
        } else {
            $l_return = $l_strFootnote . '<img class="infoIcon ' . $p_params["p_strInfoIconClass"] . ' mr5" src="' . $g_dirs["images"] . 'empty.gif" alt="' . $l_strTitle .
                '" title="' . $l_strTitle . '" height="11px" width="15px" />';
        }

        if (isys_glob_is_edit_mode()) {
            return '<span class="input-group-addon input-group-addon-unstyled">' . $l_return . '</span>';
        }

        return $l_return;
    }

    /**
     * Set the edit-mode.
     *
     * @param   boolean $p_bEditMode
     *
     * @return  $this
     */
    protected function set_edit_mode($p_bEditMode)
    {
        $this->m_bEditMode = $p_bEditMode;

        return $this;
    }

    /**
     * Retrieve the edit-mode.
     *
     * @return  boolean
     */
    protected function get_edit_mode()
    {
        return $this->m_bEditMode;
    }

    /**
     * Get HTML string for the standard attributes via the parameter-array, given as reference.
     *
     * @param   array &$p_params
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function getStandardAttributes(&$p_params)
    {
        $l_name = null;

        if ($p_params["name"] !== null) {
            $l_name = $p_params["name"];
            $p_params["name"] = 'name="' . $p_params["name"] . '"';
        }

        if ($p_params["p_strID"] !== null) {
            $p_params["p_strID"] = 'id="' . $p_params["p_strID"] . '"';
        } else {
            if ($p_params["id"] !== null) {
                $p_params["p_strID"] = 'id="' . $p_params["id"] . '"';
            } else {
                if (isset($l_name)) {
                    $p_params["p_strID"] = 'id="' . $l_name . '"';
                }
            }
        }

        if ($p_params["p_strAccessKey"] !== null) {
            $p_params["p_strAccessKey"] = 'accesskey="' . $p_params["p_strAccessKey"] . '"';
        }

        if ($p_params["type"] !== null) {
            $p_params["type"] = 'type="' . $p_params["type"] . '"';
        }

        if ($p_params["p_strValue"] !== null) {
            if (isset($p_params["p_bNoTranslation"]) && $p_params["p_bNoTranslation"]) {
                $p_params["p_strValue"] = 'value="' . $p_params["p_strValue"] . '"';
            } else {
                $p_params["p_strValue"] = 'value="' . isys_application::instance()->container->get('language')
                        ->get($p_params["p_strValue"]) . '"';
            }
        } else {
            if ($p_params["value"] !== null) {
                if ($p_params["p_bNoTranslation"] == "1") {
                    $p_params["value"] = 'value="' . $p_params["value"] . '"';
                } else {
                    $p_params["value"] = 'value="' . isys_application::instance()->container->get('language')
                            ->get($p_params["value"]) . '"';
                }
            }
        }

        if ($p_params["p_nTabIndex"] !== null) {
            $p_params["p_nTabIndex"] = 'tabindex="' . $p_params["p_nTabIndex"] . '"';
        } else {
            if ($p_params["tabindex"] !== null) {
                $p_params["p_nTabIndex"] = 'tabindex="' . $p_params["tabindex"] . '"';
            }
        }

        if ($p_params["p_strTitle"] !== null) {
            if ($p_params["p_bNoTranslation"] == "1") {
                $p_params["p_strTitle"] = 'title="' . $p_params["p_strTitle"] . '"';
            } else {
                $p_params["p_strTitle"] = 'title="' . isys_application::instance()->container->get('language')
                        ->get($p_params["p_strTitle"]) . '"';
            }
        } else {
            if ($p_params["title"] !== null) {
                if ($p_params["p_bNoTranslation"] == "1") {
                    $p_params["title"] = 'title="' . $p_params["title"] . '"';
                } else {
                    $p_params["title"] = 'title="' . isys_application::instance()->container->get('language')
                            ->get($p_params["title"]) . '"';
                }
            }
        }

        if (!empty($p_params["p_strInfoIconError"])) {
            $p_params["p_strClass"] .= ' error';
        }

        if (!empty($p_params["p_strAlt"])) {
            $p_params["p_strAlt"] = 'alt="' . $p_params["p_strAlt"] . '"';
        }

        if (!empty($p_params["p_strClass"])) {
            $p_params["p_strClass"] = 'class="' . $p_params["p_strClass"] . '"';
        }

        if (!empty($p_params["width"])) {
            $p_params["p_strStyle"] = "width:" . $p_params["width"] . ";";
        }

        if (!empty($p_params["p_strStyle"])) {
            $p_params["p_strStyle"] = 'style="' . $p_params["p_strStyle"] . '"';
        }

        if (!empty($p_params["style"])) {
            $p_params["p_strStyle"] = 'style="' . $p_params["style"] . '"';
        }

        if (!empty($p_params["p_nSize"])) {
            $p_params["p_nSize"] = 'size="' . $p_params["p_nSize"] . '"';
        }

        if (!empty($p_params["size"])) {
            $p_params["p_nSize"] = 'size="' . $p_params["size"] . '"';
        }

        if (!empty($p_params["p_nRows"])) {
            $p_params["p_nRows"] = 'rows="' . $p_params["p_nRows"] . '"';
        }

        if (!empty($p_params["p_nCols"])) {
            $p_params["p_nCols"] = 'cols="' . $p_params["p_nCols"] . '"';
        }

        if (!empty($p_params["p_nMaxLen"])) {
            $p_params["p_nMaxLen"] = 'maxlength="' . $p_params["p_nMaxLen"] . '"';
        }

        if (!empty($p_params["p_bDisabled"])) {
            $p_params["p_bDisabled"] = 'disabled="disabled"';
        }

        if (!empty($p_params["p_bReadonly"])) {
            $p_params["p_bReadonly"] = 'readonly="readonly"';
        }

        if (!empty($p_params['p_dataIdentifier'])) {
            $p_params['p_dataIdentifier'] = 'data-identifier="' . $p_params['p_dataIdentifier'] . '"';
        }

        // @see ID-2082 We use these attributes instead of CSS classes to identify form-fields with validation.
        if (!empty($p_params['p_validation_rule'])) {
            $p_params['p_validation_rule'] = 'data-validation-rule="' . $p_params['p_validation_rule'] . '"';
        }

        // @see ID-2082 We use these attributes instead of CSS classes to identify form-fields with validation.
        if (!empty($p_params['p_validation_mandatory'])) {
            $p_params['p_validation_mandatory'] = 'data-mandatory-rule="' . $p_params['p_validation_mandatory'] . '"';
        }

        if (isset($p_params['p_bEnableMetaMap'])) {
            $this->m_enableMetaMap = !!$p_params['p_bEnableMetaMap'];
        }

        if (isset($p_params['p_multiple']) && $p_params['p_multiple']) {
            $p_params['p_multiple'] = 'multiple="multiple"';
        }
    }

    /**
     * Get HTML string for the javascript attributes.
     *
     * @param   array &$p_params
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function getJavascriptAttributes(&$p_params)
    {
        if (!empty($p_params['p_onClick'])) {
            $p_params['p_onClick'] = 'onclick="' . $p_params['p_onClick'] . '"';
        } else {
            $p_params['p_onClick'] = '';
        }

        if (!empty($p_params['p_onKeyUp'])) {
            $p_params['p_onKeyUp'] = 'onkeyup="' . $p_params['p_onKeyUp'] . '"';
        } else {
            $p_params['p_onKeyUp'] = '';
        }

        if (!empty($p_params['p_onChange'])) {
            $p_params['p_onChange'] = 'onchange="' . $p_params['p_onChange'] . '"';
        } else {
            $p_params['p_onChange'] = '';
        }

        if (!empty($p_params['p_onMouseOver'])) {
            $p_params['p_onMouseOver'] = 'onmouseover="' . $p_params['p_onMouseOver'] . '"';
        } else {
            $p_params['p_onMouseOver'] = '';
        }

        if (!empty($p_params['p_onMouseOut'])) {
            $p_params['p_onMouseOut'] = 'onmouseout="' . $p_params['p_onMouseOut'] . '"';
        } else {
            $p_params['p_onMouseOut'] = '';
        }

        if (!empty($p_params['p_onMouseMove'])) {
            $p_params['p_onMouseMove'] = 'onmousemove="' . $p_params['p_onMouseMove'] . '"';
        } else {
            $p_params['p_onMouseMove'] = '';
        }

        if (!empty($p_params['p_onKeyDown'])) {
            $p_params['p_onKeyDown'] = 'onkeydown="' . $p_params['p_onKeyDown'] . '"';
        } else {
            $p_params['p_onKeyDown'] = '';
        }

        if (!empty($p_params['p_onKeyPress'])) {
            $p_params['p_onKeyPress'] = 'onkeypress="' . $p_params['p_onKeyPress'] . '"';
        } else {
            $p_params['p_onKeyPress'] = '';
        }

        if (!empty($p_params['p_onFocus'])) {
            $p_params['p_onFocus'] = 'onfocus="' . $p_params['p_onFocus'] . '"';
        } else {
            $p_params['p_onFocus'] = '';
        }

        if (!empty($p_params['p_onBlur'])) {
            $p_params['p_onBlur'] = 'onblur="' . $p_params['p_onBlur'] . '"';
        } else {
            $p_params['p_onBlur'] = '';
        }
    }

    /**
     * Method for preparing some input group parameters, if necessary.
     *
     * @param   array $params
     *
     * @return  mixed
     */
    protected function prepare_input_group(array $params = [])
    {
        if (!isset($params['disableInputGroup']) || !$params['disableInputGroup']) {
            if (!isset($params['inputGroupMarginClass']) && $params['p_bInfoIconSpacer'] !== 0 && $params['p_bInfoIconSpacer'] !== '0') {
                $params['inputGroupMarginClass'] = 'ml20';
            }

            $params['p_bInfoIconSpacer'] = 0;
            $params['inputGroupClass'] = 'input-size-normal';

            if (strpos($params["p_strClass"], 'input-block') !== false) {
                $params["p_strClass"] = str_replace('input-block', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-block';
            }

            if (strpos($params["p_strClass"], 'input-medium') !== false) {
                $params["p_strClass"] = str_replace('input-medium', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-medium';
            }

            if (strpos($params["p_strClass"], 'input-small') !== false) {
                $params["p_strClass"] = str_replace('input-small', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-small';
            }

            if (strpos($params["p_strClass"], 'input-mini') !== false) {
                $params["p_strClass"] = str_replace('input-mini', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-mini';
            }

            if (strpos($params["p_strClass"], 'input-dual-large') !== false) {
                $params["p_strClass"] = str_replace('input-dual-large', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-medium';
            }

            if (strpos($params["p_strClass"], 'input-dual-small') !== false) {
                $params["p_strClass"] = str_replace('input-dual-small', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-mini';
            }

            if (strpos($params["p_strClass"], 'input-dual-radio') !== false) {
                $params["p_strClass"] = str_replace('input-dual-radio', '', $params["p_strClass"]);
                $params['inputGroupClass'] = 'input-size-medium';
            }
        }

        return $params;
    }

    /**
     * Method for rendering the "input-group" wrapper, if necessary.
     *
     * @param   string $output
     * @param   array  $params
     *
     * @return  string
     */
    protected function render_input_group($output, array $params = [])
    {
        if (!isset($params['disableInputGroup']) && !$params['disableInputGroup']) {
            return '<div class="' . $params['inputGroupMarginClass'] . ' input-group ' . $params['inputGroupClass'] . '">' . implode('', $this->inputGroupAddons['before']) .
                $output . implode('', $this->inputGroupAddons['after']) . '</div>';
        }

        return $output;
    }

    /**
     * Method for adding a input group addon before the field.
     *
     * @param string $html
     *
     * @return $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function addInputGroupAddonBefore($html)
    {
        $this->inputGroupAddons['before'][] = $html;

        return $this;
    }

    /**
     * Method for adding a input group addon after the field.
     *
     * @param string $html
     *
     * @return $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function addInputGroupAddonAfter($html)
    {
        $this->inputGroupAddons['after'][] = $html;

        return $this;
    }
}
