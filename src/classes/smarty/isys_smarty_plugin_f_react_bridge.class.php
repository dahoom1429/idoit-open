<?php
/**
 * React bridge
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class isys_smarty_plugin_f_react_bridge extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    private static $i = 0;

    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        $component = $p_params['component'];
        $registry = isys_application::instance()->container->get('components.registry');
        if (!$registry) {
            return '';
        }
        $prefixes = explode('.', $component);
        do {
            $path = $registry->find(implode('.', $prefixes));
            array_pop($prefixes);
        } while (!$path && count($prefixes) > 0);
        if (!$path) {
            return "<div>Component $component cannot be found</div>";
        }
        $params = base64_encode(json_encode($p_params['params'] ? $p_params['params'] : []));
        $id = 'react-' . (self::$i++);

        return "<div id='$id'></div>
        <script type='text/javascript'>
idoit.Require.require(['reactBridge'], function() {
	ReactBridge.render('$id', '$component', '$path', JSON.parse(atob('$params')));
});
</script>";
    }

    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    }
}
