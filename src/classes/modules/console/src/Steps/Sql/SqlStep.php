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

namespace idoit\Module\Console\Steps\Sql;

use idoit\Module\Console\Steps\Step;
use mysqli;

abstract class SqlStep implements Step
{
    private $host;

    private $name;

    private $password;

    private $port;

    private $username;

    const POSSIBLE_LOCAL_HOSTS = [
        'localhost',
        '127.0.0.1',
        '::1'
    ];

    public function __construct(
        $host,
        $username,
        $password,
        $name,
        $port
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->port = $port;
    }

    protected function createConnection()
    {
        return new mysqli($this->host, $this->username, $this->password, $this->name, $this->port);
    }

    /**
     * @return string
     */
    public function getHostForUser()
    {
        $grantHost = '%';
        if (in_array($this->host, self::POSSIBLE_LOCAL_HOSTS)) {
            $grantHost = 'localhost';
        }

        return $grantHost;
    }
}
