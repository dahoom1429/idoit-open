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

class ArrayOption extends Option
{
    /**
     * @var Option
     */
    private $option;

    public function __construct(Option $option)
    {
        parent::__construct($option->getSettingName(), $option->getDescription(), $option->getValue(), $option->getOption());
        $this->option = $option;
    }

    public function ask(OutputStyle $style)
    {
        $baseDescription = $this->option->description;

        $v = [];
        do {
            if (count($v)) {
                $this->option->description = $baseDescription . ' (current queue: ' . implode(', ', $v) . '), to proceed leave blank';
            }

            $value = $this->option->ask($style);

            if (empty($value)) {
                break;
            }
            $v[] = $value;
        } while (!empty($value));

        return $v;
    }
}
