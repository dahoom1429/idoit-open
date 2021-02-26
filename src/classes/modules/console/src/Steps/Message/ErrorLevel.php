<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps\Message;

class ErrorLevel
{
    const INFO         = 0b1;
    const DEBUG        = 0b10;
    const NOTIFICATION = 0b100;
    const ERROR        = 0b1000;
    const FATAL        = 0b10000;
}
