<?php

namespace idoit\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IsysLogWrapper
 *
 * @deprecated  Please don't use this class
 * @package idoit\Console\Command
 */
class IsysLogWrapper extends \isys_log
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Map console verbosity levels to legacy levels
     *
     * @var array
     */
    private static $verbosityMapping = [
        OutputInterface::VERBOSITY_QUIET        => self::C__NONE,
        OutputInterface::VERBOSITY_NORMAL       => self::C__FATAL,
        OutputInterface::VERBOSITY_VERBOSE      => self::C__WARNING,
        OutputInterface::VERBOSITY_VERY_VERBOSE => self::C__INFO,
        OutputInterface::VERBOSITY_DEBUG        => self::C__ALL
    ];

    /**
     * Constructor of parent is protected
     *
     * @return IsysLogWrapper
     */
    public static function instance()
    {
        return new self();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        $this->m_auto_flush = true;
        $this->m_log_file = '';
        $this->m_log_level = self::$verbosityMapping[$output->getVerbosity()];
        $this->m_verbose_level = self::$verbosityMapping[$output->getVerbosity()];
    }

    public function flush_log($p_write = true, $p_standalone = true)
    {
        // Disable writing log file
        $logContent = parent::flush_log(false, $p_standalone);

        if (!empty($logContent)) {
            $this->output->writeln($logContent);
        }
    }

    public function flush_verbosity($p_print = true, $p_standalone = true)
    {
        // Disable writing into STDERR
        $content = parent::flush_verbosity(false, $p_standalone);

        if (!empty($content)) {
            $this->output->writeln($content);
        }
    }
}
