<?php

/**
 * i-doit
 *
 * HTTP protocol
 *
 * @package     i-doit
 * @subpackage  Protocol
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_protocol_http extends isys_protocol
{
    const C__HTTP      = 'http';
    const C__HTTPS     = 'https';
    const HTTP_OK      = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;

    protected $m_protocol = null;

    private $m_base_url = '/';

    private $m_headers = null;

    /* HTTP types */
    private $m_host = null;

    private $m_pass = null;

    /* HTTP codes */
    private $m_port = null;

    private $m_requests = [];

    private $m_user = null;

    /**
     * Return instance of isys_protocol_http
     *
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_protocol
     *
     * @return isys_protocol_http
     */
    public static function get_instance($p_host, $p_port = 80, $p_protocol = self::C__HTTP)
    {
        return new self($p_host, $p_port, $p_protocol);
    }

    public function get_port()
    {
        return $this->m_port;
    }

    public function set_port($p_port)
    {
        $this->m_port = $p_port;

        return $this;
    }

    /**
     * Returns the Host without any information
     */
    public function get_host()
    {
        return $this->m_protocol . "://" . $this->m_host;
    }

    /**
     * Sets user
     *
     * @param   string $p_user
     *
     * @return  isys_protocol_http
     */
    public function set_user($p_user)
    {
        $this->m_user = $p_user;

        return $this;
    }

    /**
     * Sets password
     *
     * @param string $p_pass
     *
     * @return isys_protocol_http
     */
    public function set_pass($p_pass)
    {
        $this->m_pass = $p_pass;

        return $this;
    }

    /**
     * Sets the base url. This url is added in front of every request.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_http
     */
    public function set_base_url($p_base_url)
    {
        $this->m_base_url = $p_base_url;

        return $this;
    }

    /**
     * Retrieve base url.
     *
     * @return  string
     */
    public function get_base_url()
    {
        return $this->m_base_url;
    }

    /**
     * Attach something to the base URL.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_http
     */
    public function attach_base_url($p_base_url)
    {
        $this->m_base_url .= $p_base_url;

        return $this;
    }

    /**
     * Set headers.
     *
     * @param   string $p_headers
     *
     * @return  isys_protocol_http
     */
    public function set_headers($p_headers)
    {
        $this->m_headers = $p_headers;

        return $this;
    }

    /**
     * Opens a standard get connection to the base url.
     *
     * @return string
     */
    public function open()
    {
        return $this->get('');
    }

    /**
     * Post a request
     *
     * @param string $p_path
     * @param array  $p_params
     *
     * @return string
     */
    public function post($p_path, $p_params = [])
    {
        $this->m_requests[] = $this->request('POST', $this->url($p_path), $p_params);

        return $this->m_requests[count($this->m_requests) - 1];
    }

    /**
     * Gets a request
     *
     * @param   string $p_path
     * @param   array  $p_params
     *
     * @return  string
     */
    public function get($p_path, $p_params = [])
    {
        $this->m_requests[] = $this->request('GET', $this->url($p_path), $p_params);

        return $this->m_requests[count($this->m_requests) - 1];
    }

    /**
     * Get request array.
     *
     * @return  array
     */
    public function get_requests()
    {
        return $this->m_requests;
    }

    /**
     * url()-Wrapper
     *
     * @return  string
     */
    public function get_url()
    {
        return $this->url();
    }

    /**
     * @return  string
     */
    public function get_protocol()
    {
        return $this->m_protocol;
    }

    /**
     * Starts the HTTP request.
     *
     * @param   string $p_type
     * @param   string $l_url
     * @param   array  $p_params
     *
     * @return  string
     * @throws  isys_exception_api
     * @throws  isys_exception_general
     */
    private function request($p_type, $l_url, $p_params = [])
    {
        if (function_exists('curl_init')) {
            $this->m_connection = curl_init();

            // @todo This is only useful for HTTP Base Authentication! Maybe it's better to use various authentication methods (+ cookie support).
            if (isset($this->m_user)) {
                $l_userpwd = $this->m_user;
                if (isset($this->m_pass)) {
                    $l_userpwd .= ':' . $this->m_pass;
                }

                curl_setopt($this->m_connection, CURLOPT_USERPWD, $l_userpwd);
            }

            switch ($p_type) {
                case 'POST':
                    curl_setopt($this->m_connection, CURLOPT_URL, $l_url);
                    curl_setopt($this->m_connection, CURLOPT_POST, true);
                    curl_setopt($this->m_connection, CURLOPT_POSTFIELDS, $p_params);
                    break;
                case 'GET':
                    $l_url .= strstr($l_url, '?') ? '&' : '?';
                    curl_setopt($this->m_connection, CURLOPT_URL, $l_url . http_build_query($p_params));
                    break;
            }

            // Return the transfer as a string of the return value of curl_exec() instead of outputting it out directly:
            curl_setopt($this->m_connection, CURLOPT_RETURNTRANSFER, true);

            // Send header:
            if (isset($this->m_headers)) {
                curl_setopt($this->m_connection, CURLOPT_HTTPHEADER, $this->m_headers);
            }

            $l_exec = curl_exec($this->m_connection);
            $l_status = curl_getinfo($this->m_connection, CURLINFO_HTTP_CODE);

            switch ($l_status) {
                case self::HTTP_OK:
                case self::HTTP_CREATED:
                case self::HTTP_ACEPTED:
                    return $l_exec;
                default:
                    throw new isys_exception_api("http error: {$l_status}", $l_status);
            }
        } else {
            throw new isys_exception_general('php-curl extension is required.');
        }
    }

    /**
     * Get a http url.
     *
     * @param   string $p_path
     *
     * @return  string
     */
    private function url($p_path = null)
    {
        return "{$this->m_protocol}://{$this->m_host}:{$this->m_port}{$this->m_base_url}{$p_path}";
    }

    /**
     * Singleton constructor.
     *
     * @param  string  $p_host
     * @param  integer $p_port
     * @param  string  $p_protocol
     */
    protected function __construct($p_host, $p_port, $p_protocol)
    {
        $this->m_host = $p_host;
        $this->m_port = $p_port;
        $this->m_protocol = $p_protocol;
    }
}
