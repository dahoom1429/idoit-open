<?php

/**
 * i-doit
 *
 * Factory for logger.
 *
 * @deprecated  Please use something else!
 * @package     i-doit
 * @subpackage  Log
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory_log extends isys_factory
{
    /**
     * @var array
     */
    protected static $topics = [];

    /**
     * @param  string $topic
     * @param  null   $unused
     *
     * @deprecated
     * @return isys_log|mixed
     */
    public static function get_instance($topic, $unused = null)
    {
        global $g_config, $g_product_info;

        $logger = isys_log::get_instance($topic);

        if (!in_array($topic, self::$topics, true)) {
            $logFile = $g_config['base_dir'] . 'log/' . $topic . '_' . date('Y-m-d_H_i_s') . '.log';

            $l_header = '# i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['type'] . PHP_EOL .
                '# host URL ' . C__HTTP_HOST . PHP_EOL .
                '# log for "' . $topic . '"' . PHP_EOL .
                '# started at ' . date('c') . PHP_EOL .
                '# written to "' . $logFile . '"' . PHP_EOL;

            $logger->set_log_level(isys_log::C__ALL & ~isys_log::C__DEBUG)
                ->set_verbose_level(isys_log::C__FATAL | isys_log::C__ERROR | isys_log::C__WARNING | isys_log::C__NOTICE)
                ->set_log_file($logFile)
                ->set_header($l_header);

            self::$topics[] = $topic;
        }

        return $logger;
    }
}
