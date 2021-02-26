<?php
/**
 * @author      Dennis Stücken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

try {
    $l_opt = getopt("mf:i:a:");

    if (isset($l_opt['a'])) {
        switch ($l_opt['a']) {
            case 'installModule':
                if (isset($l_opt['f'])) {
                    if (file_exists($l_opt['f'])) {
                        global $g_comp_database_system, $l_dao_mandator;
                        $l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);

                        if (install_module_by_zip($l_opt['f'], isset($l_opt['i']) ? $l_opt['i'] : null)) {
                            echo "Add-on installed/updated.\n";
                            die;
                        } else {
                            throw new Exception('Could not install add-on. File "' . $l_opt['f'] . '" was not found');
                        }
                    }
                }
                break;
        }
    }

    throw new Exception("Usage: php index.php -a installModule -f modulefile.zip [-i mandatorID]");
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
