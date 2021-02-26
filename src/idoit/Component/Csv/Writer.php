<?php

namespace idoit\Component\Csv;

use isys_tenantsettings;
use League\Csv\EncloseField;
use League\Csv\Writer as CsvWriter;
use SplFileObject;

/**
 * i-doit CSV reader.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.13
 */
class Writer extends CsvWriter
{
    /**
     * @param  string $path
     * @param  string $openMode
     *
     * @return self
     */
    public static function createFromPath($path, $openMode = null)
    {
        $writer = parent::createFromPath($path, $openMode ?: 'r+')->configure();
        EncloseField::addTo($writer, "\t\x1f");
        return $writer;
    }

    /**
     * @param  string $string
     * @param  string $newline
     *
     * @return self
     */
    public static function createFromString($string, $newline = null)
    {
        $writer = parent::createFromString($string, $newline ?: "\n")->configure();
        EncloseField::addTo($writer, "\t\x1f");
        return $writer;
    }

    /**
     * Forced enclosure does not work with SplFileObject
     *
     * @param  SplFileObject $file
     *
     * @return self
     */
    public static function createFromFileObject(SplFileObject $file)
    {
        return parent::createFromFileObject($file)->configure();
    }

    /**
     * @return Writer
     */
    private function configure()
    {
        return $this
            ->setOutputBOM(self::BOM_UTF8)
            ->setDelimiter(isys_tenantsettings::get('system.csv-export-delimiter', ';'));
    }
}
