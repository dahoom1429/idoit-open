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

namespace idoit\Module\Console\Steps;

use Composer\Semver\Semver;
use idoit\Module\Console\Steps\Message\ErrorLevel;

class VersionCheck extends Check
{
    /**
     * @var array
     */
    private $constraints;

    private $name;

    private $version;

    /**
     * PhpVersionCheck constructor.
     *
     * @param            $name
     * @param            $version
     * @param array      $constraints
     * @param ErrorLevel $errorLevel
     */
    public function __construct($name, $version, array $constraints, int $errorLevel = ErrorLevel::INFO)
    {
        $this->name = $name;
        $this->version = $version;
        $this->constraints = $constraints;
        $this->level = $errorLevel;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->name . ' Version ' . $this->version . ': ';
        $name .= implode(', ', $this->constraints);

        return $name;
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    public function check()
    {
        $version = $this->version;
        $result = array_reduce($this->constraints, function ($carry, $item) use ($version) {
            return $carry && Semver::satisfies($version, $item);
        }, true);

        return $result;
    }
}
