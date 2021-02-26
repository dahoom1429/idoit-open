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

use Exception;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\OutputStyle;

class Option
{
    protected $settingName;

    protected $description;

    /**
     * @var InputOption
     */
    private $option;

    private $value;

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     *
     * @return Option
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function __construct($settingName, $description, $default = null, InputOption $option = null)
    {
        $this->settingName = $settingName;
        $this->description = $description;
        $this->value = $default;
        if ($option !== null) {
            $option->setDefault($default);
            $this->option = $option;
        }
    }

    public function ask(OutputStyle $style)
    {
        $required = $this->option !== null && $this->option->isValueRequired();
        return $style->ask("Please, enter {$this->description}", $this->value, function ($v) use ($required) {
            if ($required && empty($v)) {
                throw new Exception('A value is required');
            }
            return $v;
        });
    }

    /**
     * @return InputOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @return mixed
     */
    public function getSettingName()
    {
        return $this->settingName;
    }
}
