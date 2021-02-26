<?php

/**
 * i-doit
 *
 * Smarty plugin for time input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Illia Polianskyi
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_time extends isys_smarty_plugin_f_text implements isys_smarty_plugin
{

    /**
     * @inheritDoc
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        // Default css class.
        $p_params['p_strClass'] = 'input ' . (isset($p_params['p_strClass']) ? $p_params['p_strClass'] : '');
        $p_params['p_strPlaceholder'] = 'hh:mm';
        $p_params['p_strClass'] = 'input input-mini';
        $p_params['inputGroupClass'] = 'input-size-mini';

        $jsValue = str_ireplace('value="', '', $p_params['p_strValue']);
        $jsValueLen = strlen($jsValue);
        if (substr($jsValue, $jsValueLen-2, 1) === '"') {
            $jsValue = substr($jsValue, 0, $jsValueLen-1);
        }
        $jsValue = $jsValue ? $jsValue : '__:__';
        // add js code for mask and validation
        $l_strOut = "
<script type=\"text/javascript\">
\"use strict\";
idoit.Require.require('smartyTime', function () { 
    var me = $$('input[name=\"" . $p_params['name'] . "\"]')[0];    
    if (!me) {
        return;    
    }
    if (me.getAttribute('disabled')) {
        return;    
    }
    var value = '" . $jsValue . "';
    var smartyTime = new SmartyTime();
    smartyTime.init(me, value); 
});
</script>            
            ";

        return parent::navigation_edit($p_tplclass, $p_params) . $l_strOut;
    }
}
