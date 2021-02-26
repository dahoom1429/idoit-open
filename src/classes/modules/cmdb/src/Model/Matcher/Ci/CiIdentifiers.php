<?php

namespace idoit\Module\Cmdb\Model\Matcher\Ci;

use idoit\Exception\Exception;
use idoit\Module\Cmdb\Model\Matcher\AbstractIdentifier;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiIdentifiers
{
    /**
     * @var AbstractIdentifier[]
     */
    protected $identifiers;

    /**
     * @return \idoit\Module\Cmdb\Model\Matcher\AbstractIdentifier
     */
    public function getIdentifier($key)
    {
        if (!isset($this->identifiers[$key])) {
            throw new Exception(sprintf('Identifier of type %s does not exist', $key));
        }

        return $this->identifiers[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasIdentifier($key)
    {
        return isset($this->identifiers[$key]);
    }

    /**
     * @return \idoit\Module\Cmdb\Model\Matcher\AbstractIdentifier[]
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * @param \idoit\Module\Cmdb\Model\Matcher\AbstractIdentifier[] $identifiers
     *
     * @return $this
     */
    public function setIdentifiers($identifiers)
    {
        $this->identifiers = $identifiers;

        return $this;
    }

    /**
     * Initialize Match Identifiers
     *
     * @param $identfierFlags
     */
    public function initialize($identfierFlags = null)
    {
        // Only attach identifiers that match the $identfierFlags
        if (is_dir(__DIR__ . DS . 'Identifier')) {
            $handle = opendir(__DIR__ . DS . 'Identifier');
            while ($file = readdir($handle)) {
                if (strlen($file) > 4 && file_exists(__DIR__ . DS . 'Identifier' . DS . $file)) {
                    include_once(__DIR__ . DS . 'Identifier' . DS . $file);
                    $class = "idoit\\Module\\Cmdb\\Model\\Matcher\\Identifier\\" . substr($file, 0, strpos($file, '.'));
                    $identifier = new $class;
                    if ($identifier::getBit() & $identfierFlags || $identfierFlags === null) {
                        $this->identifiers[$identifier::KEY] = $identifier;
                    }
                }
            }
        }
    }

    /**
     * CiImportIdentifiers constructor.
     */
    public function __construct($identfierFlags = null)
    {
        $this->initialize($identfierFlags);
    }
}
