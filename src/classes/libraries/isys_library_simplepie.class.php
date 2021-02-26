<?php

/**
 * i-doit
 *
 * SimplePie wrapper
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_library_simplepie extends SimplePie
{
    /**
     * Adding proxy method to support proxies in simplepie
     *
     * See Jira ID-3585 and Rollbar #4383.
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     *
     * @return $this
     */
    public function set_proxy($host, $port, $user = null, $pass = null)
    {
        $curlOptions = [
            CURLOPT_PROXY     => $host,
            CURLOPT_PROXYPORT => $port,
        ];

        if ($user) {
            $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_ANYSAFE;
            $curlOptions[CURLOPT_PROXYUSERPWD] = sprintf('%s:%s', $user, $pass);
        }

        $this->set_curl_options($curlOptions);

        return $this;
    }
}