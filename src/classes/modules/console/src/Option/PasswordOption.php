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

namespace idoit\Module\Console\Option;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\OutputStyle;

class PasswordOption extends Option
{
    /**
     * @var bool
     */
    private $required;

    public function __construct($settingName, $description, $default = null, InputOption $option = null, $required = true)
    {
        parent::__construct(
            $settingName,
                            $description,
                            $default,
                            $option
        );
        $this->required = $required;
    }

    public function ask(OutputStyle $style)
    {
        $required = $this->required;
        return $style->askHidden(
            "Please, enter {$this->description}",
            function ($result) use ($required) {
                if (!$required) {
                    return $result;
                }
                if (!$required || strlen($result) > 0) {
                    return $result;
                }
                throw new \Exception('Password should not be empty!');
            }
        );
    }
}
