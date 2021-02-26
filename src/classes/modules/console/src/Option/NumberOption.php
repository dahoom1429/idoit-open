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

use Symfony\Component\Console\Style\OutputStyle;

class NumberOption extends Option
{
    public function ask(OutputStyle $style)
    {
        $required = $this->getOption() !== null && $this->getOption()->isValueRequired();
        return $style->ask(
            "Please, enter {$this->description}",
            $this->getValue(),
            function ($result) use ($required) {
                if (!$required && empty($result)) {
                    return $result;
                }
                if ($result === (string)(int)$result) {
                    return $result;
                }
                throw new \Exception('Value should be a number!');
            }
        );
    }
}
