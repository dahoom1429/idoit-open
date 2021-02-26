<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Logger;
use isys_application;
use isys_exception;
use isys_helper_upload;
use Monolog\Handler\StreamHandler;

class DataRetrieverException extends isys_exception
{
    /**
     * @var  string
     */
    protected $m_exception_topic = 'Merger';

    /**
     * @var Logger
     */
    private static $logger = null;

    /**
     * DataRetrieverException constructor.
     *
     * @param        $message
     * @param string $extraInfo
     * @param int    $code
     */
    public function __construct($message, $extraInfo = '', $code = 0)
    {
        parent::__construct(
            $message,
            $extraInfo,
            $code,
            '',
            true
        );
    }

    /**
     * This method will be used to write the exception log. It will only be written, when the exception reaches the GUI.
     * Meaning: It will only be written, if it isn't catched by any specific code.
     *
     * @return $this
     * @throws \Exception
     */
    public function write_log()
    {
        $session = isys_application::instance()->container->get('session');

        if (self::$logger === null) {
            $path = isys_application::instance()->app_path;
            $tenant = isys_helper_upload::prepare_filename($session->get_mandator_name());
            $date = date('Y-m-d');

            self::$logger = new Logger('Merger', [new StreamHandler("{$path}/log/sync-merger__{$tenant}__{$date}.log")]);
        }

        self::$logger->error('Triggered by "' . $session->get_current_username() . '": ' . $this->getMessage());

        return $this;
    }
}
