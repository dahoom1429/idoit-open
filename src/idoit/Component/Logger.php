<?php

namespace idoit\Component;

use isys_application;
use Monolog\Handler\StreamHandler;

/**
 * i-doit Logger - extends the brilliant Monolog class with a few own methods (mostly used for the GUI).
 *
 * @package    i-doit
 * @subpackage Component
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Logger extends \Monolog\Logger
{
    /**
     * @param string $name
     * @param string $logPath
     *
     * @return Logger
     */
    public static function factory($name, $logPath)
    {
        $logHandler = new StreamHandler($logPath, Logger::WARNING);

        $log = new self($name);
        $log->pushHandler($logHandler);

        return $log;
    }

    /**
     * Method for retrieving a fitting icon for every log level.
     *
     * @param integer $level
     *
     * @static
     * @return string|array
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelIcons($level = null)
    {
        global $g_dirs;

        $icons = [
            self::DEBUG     => $g_dirs["images"] . 'icons/silk/bug.png',
            self::INFO      => $g_dirs["images"] . 'icons/silk/information.png',
            self::NOTICE    => $g_dirs["images"] . 'icons/silk/lightbulb.png',
            self::WARNING   => $g_dirs["images"] . 'icons/silk/error.png',
            self::ERROR     => $g_dirs["images"] . 'icons/alert-icon.png',
            self::CRITICAL  => $g_dirs["images"] . 'icons/alert-icon.png',
            self::ALERT     => $g_dirs["images"] . 'icons/silk/delete.png',
            self::EMERGENCY => $g_dirs["images"] . 'icons/silk/cross.png'
        ];

        if ($level !== null) {
            return $icons[$level];
        }

        return $icons;
    }

    /**
     * Method for retrieving a fitting text-color (via CSS class) for every log level.
     *
     * @param integer $level
     *
     * @static
     * @return string|array
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelColors($level = null)
    {
        $colors = [
            self::DEBUG     => 'green',
            self::INFO      => 'blue',
            self::NOTICE    => '',
            self::WARNING   => 'yellow',
            self::ERROR     => 'red',
            self::CRITICAL  => 'red',
            self::ALERT     => 'red',
            self::EMERGENCY => 'red'
        ];

        if ($level !== null) {
            return $colors[$level];
        }

        return $colors;
    }

    /**
     * Gets log level as string.
     *
     * @param int $level
     *
     * @static
     * @return string|array
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelNames($level = null)
    {
        $language = isys_application::instance()->container->get('language');

        $names = [
            self::DEBUG     => $language->get('LC_UNIVERSAL__LOG_LEVEL__DEBUG'),
            self::INFO      => $language->get('LC_UNIVERSAL__LOG_LEVEL__INFO'),
            self::NOTICE    => $language->get('LC_UNIVERSAL__LOG_LEVEL__NOTICE'),
            self::WARNING   => $language->get('LC_UNIVERSAL__LOG_LEVEL__WARNING'),
            self::ERROR     => $language->get('LC_UNIVERSAL__LOG_LEVEL__ERROR'),
            self::CRITICAL  => $language->get('LC_UNIVERSAL__LOG_LEVEL__CRITICAL'),
            self::ALERT     => $language->get('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
            self::EMERGENCY => $language->get('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
        ];

        if ($level !== null) {
            return $names[$level];
        }

        return $names;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addDebug($message, array $context = []): bool
    {
        parent::debug($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addInfo($message, array $context = []): bool
    {
        parent::info($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addNotice($message, array $context = []): bool
    {
        parent::notice($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addWarning($message, array $context = []): bool
    {
        parent::warning($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function warn($message, array $context = []): bool
    {
        parent::warning($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addError($message, array $context = []): bool
    {
        parent::error($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function err($message, array $context = []): bool
    {
        parent::error($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addCritical($message, array $context = []): bool
    {
        parent::critical($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function crit($message, array $context = []): bool
    {
        parent::critical($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addAlert($message, array $context = []): bool
    {
        parent::alert($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function addEmergency($message, array $context = []): bool
    {
        parent::emergency($message, $context);

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @deprecated Implemented due to compatibility, see ID-7538. Will be removed in i-doit 1.18.
     */
    public function emerg($message, array $context = []): bool
    {
        parent::emergency($message, $context);

        return true;
    }
}
