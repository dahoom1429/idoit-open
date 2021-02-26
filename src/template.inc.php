<?php
/**
 * Assign some template variables
 *
 * @package     i-doit
 * @subpackage  General
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @todo        move to initialization method in isys_component_template
 */
global $g_config, $g_dirs, $index_includes;

$template = isys_application::instance()->container->get('template');

// Assign almighty index_includes array.
$template->assign('index_includes', $index_includes);

// Analyze parameters for object lists.
$sortParameter = isys_glob_get_param('sort');
$directoryParameter = isys_glob_get_param('dir');

if (!$sortParameter) {
    $template->assign('sort', $sortParameter);
}

if (!$directoryParameter) {
    $template->assign('dir', $directoryParameter);
}

// Exception handling.
if (!empty($g_error)) {
    if (is_string($g_error)) {
        $g_error = nl2br($g_error);
    }

    $template->assign('g_error', $g_error);
}
