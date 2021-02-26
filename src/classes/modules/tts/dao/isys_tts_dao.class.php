<?php

/**
 * i-doit
 *
 * RT DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tts_dao extends isys_component_dao
{
    /**
     * @var  mixed
     */
    static private $m_data = null;

    /**
     * Return an associative array of an Request Tracker - Server stored in isys_tts_config given by its ID $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data()
    {
        return $this->retrieve('SELECT * FROM isys_tts_config INNER JOIN isys_tts_type ON isys_tts_type__id = isys_tts_config__isys_tts_type__id LIMIT 1;');
    }

    /**
     * Retrieve ticket system types.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_tts_types($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_tts_type WHERE TRUE';

        if ($p_id !== null) {
            $l_sql .= ' AND isys_tts_type__id = ' . $this->convert_sql_id($p_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Return config as array.
     *
     * @throws  isys_exception_general
     * @return  array
     */
    public function get_config()
    {
        if (self::$m_data === null) {
            self::$m_data = $this->get_data()
                ->get_row();
        }

        if (count(self::$m_data) === 0) {
            throw new isys_exception_general('TTS connector is not configured.');
        }

        return isys_tts_dao::$m_data;
    }

    /**
     * Returns corresponding protocol for current TTS configuration
     *
     * @return isys_protocol
     * @throws Exception
     */
    public function get_protocol()
    {
        /* Get and format config variables */
        $l_config = $this->get_config();
        $l_register = parse_url($l_config['isys_tts_config__service_url']);

        if (isset($l_register['host'])) {
            $l_url = $l_register['host'];
            $l_path = @$l_register['path'];
            $l_user = @$l_register['user'];
            $l_pass = @$l_register['pass'];
            $l_port = @$l_register['port'];
        } else {
            $l_url = $l_config['isys_tts_config__service_url'];
            $l_path = '';
            $l_user = null;
            $l_pass = null;
        }

        $l_port = (!empty($l_port) && is_numeric($l_port)) ? $l_port : ($l_register['scheme'] == 'https' ? 443 : 80);

        /* Get protocoll class */
        $l_protocol = $l_config['isys_tts_type__protocol'];

        /* Return instance of appropriate protocol */
        if (!class_exists($l_protocol)) {
            new isys_exception_general(sprintf('TTS connection protocol "%s" not found', $l_protocol));
        }

        return call_user_func([
            $l_protocol,
            'get_instance'
        ], $l_url)
            ->set_base_url($l_path)
            ->set_user($l_user)
            ->set_pass($l_pass)
            ->set_protocol($l_register['scheme'])
            ->set_port($l_port);
    }

    /**
     * Returns corresponding connector for current TTS configuration
     *
     * @return isys_connector_ticketing
     * @throws Exception
     */
    public function get_connector()
    {
        try {
            $l_config = $this->get_config();

            /* Get class of connector */
            $l_connector = $l_config['isys_tts_type__connector'];

            /* Return instance of appropriate connector */
            if (!class_exists($l_connector)) {
                throw new isys_exception_general(sprintf('TTS connector "%s" not found', $l_connector));
            }

            $l_connector_instance = new $l_connector($this->get_protocol());

            return $l_connector_instance->set_user($l_config['isys_tts_config__user'])
                ->set_pass($l_config['isys_tts_config__pass']);

        } catch (isys_exception_general $e) {
            throw $e;
        }
    }

    /**
     * Save config.
     *
     * @param   integer $p_active
     * @param   array   $p_tts_type
     * @param   string  $p_service_url
     * @param   string  $p_user
     * @param   string  $p_pass
     *
     * @return  boolean
     */
    public function save($p_active, $p_tts_type, $p_service_url, $p_user, $p_pass = null)
    {
        $this->begin_update();

        $l_config = $this->get_data()
            ->get_row();

        if (is_array($l_config) && count($l_config) > 0) {
            $l_type = "UPDATE";
        } else {
            $l_type = "INSERT INTO";
        }

        $l_query = $l_type . " isys_tts_config
			SET isys_tts_config__isys_tts_type__id = " . $this->convert_sql_id($p_tts_type) . ",
			isys_tts_config__active = " . $this->convert_sql_id($p_active) . ",
			isys_tts_config__service_url = " . $this->convert_sql_text($p_service_url) . ",
			isys_tts_config__user = " . $this->convert_sql_text($p_user);

        if ($p_pass !== null) {
            $l_query .= ', isys_tts_config__pass = ' . $this->convert_sql_text($p_pass);
        }

        return ($this->update($l_query . ';') && $this->apply_update());
    }
}