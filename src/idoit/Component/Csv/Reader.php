<?php

namespace idoit\Component\Csv;

use isys_tenantsettings;
use League\Csv\Reader as CsvReader;
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
class Reader extends CsvReader
{
    /**
     * @param  string $path
     * @param  string $openMode
     *
     * @return self
     */
    public static function createFromPath($path, $openMode = null)
    {
        return parent::createFromPath($path, $openMode ?: 'r+')->configure();
    }

    /**
     * @param  string $string
     * @param  string $newline
     *
     * @return self
     */
    public static function createFromString($string, $newline = null)
    {
        return parent::createFromString($string, $newline ?: "\n")->configure();
    }

    /**
     * @param  SplFileObject $file
     *
     * @return self
     */
    public static function createFromFileObject(SplFileObject $file)
    {
        return parent::createFromFileObject($file)->configure();
    }

    /**
     * @return Reader
     */
    private function configure()
    {
        return $this
            ->setOutputBOM(self::BOM_UTF8)
            ->setDelimiter(isys_tenantsettings::get('system.csv-export-delimiter', ';'));
    }
}
