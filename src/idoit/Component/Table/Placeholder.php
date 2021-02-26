<?php

namespace idoit\Component\Table;

use idoit\Exception\Exception;
use isys_application;
use isys_convert;

/**
 * i-doit Placeholder Component.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Oscar Pohl <opohl@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Placeholder
{
    /**
     * @var bool|mixed
     */
    private $linkEmailAddresses;

    /**
     * @var bool|mixed
     */
    private $linkObjects;

    public function __construct($linkObjects = true, $linkEmailAddresses = true)
    {
        $this->linkObjects = $linkObjects;
        $this->linkEmailAddresses = $linkEmailAddresses;
    }

    /**
     * Replaces placeholder example: {mem,1073741824,GB}
     *
     * @param $value
     * @param $unit
     *
     * @return mixed
     */
    private function replaceMemory($value, $unit)
    {
        return round(isys_convert::memory($value, 'C__MEMORY_UNIT__' . $unit, C__CONVERT_DIRECTION__BACKWARD), 4) . ' ' . $unit;
    }

    /**
     * Replace placeholder example: {currency,25000.42,1}
     *
     * @param $value
     *
     * @return mixed
     */
    private function replaceCurrency($value)
    {
        return isys_application::instance()->container->get('locales')->fmt_monetary($value);
    }

    /**
     * Replace placeholders for a single datum
     *
     * @param $html
     *
     * @return string
     */
    public function replacePlaceholdersInCell($html)
    {
        global $g_dirs;
        $html = preg_replace([
            '/[\w\- ]+ \{1\}/', // Replace the "Title {1}" with the root location house.
            '/\{(#[0-9a-fA-F]{3,6})\}/', // Replace "{#123456}" with the cmdb-marker.
            '/([^\{\>,]+) \{([0-9]+)\}/' // Replace object links with format "Title {id}".
        ], [
            '<img class="vam" src="' . $g_dirs['images'] . 'icons/silk/house.png">',
            '<div class="dynamic-replacement cmdb-marker" style="background-color: $1;"></div>',
            ' <a class="dynamic-replacement quickinfo" href="?objID=$2" data-object-id="$2">$1</a>'
        ], $html);
        $html = preg_replace_callback('/\{([a-z]*)\,([0-9a-zA-Z\.]*)\,([0-9a-zA-Z\.]*)\}/', function ($matches) {
            switch ($matches[1]) {
                case 'mem':
                    return $this->replaceMemory($matches[2], $matches[3]);
                case 'currency':
                    return $this->replaceCurrency($matches[2]);
                default:
                    return $matches[0];
            }
        }, $html);

        // ID-6376 Detect e-mail links and add some css classes to it
        if ($this->linkEmailAddresses) {
            preg_match('/((([^<>()\[\]\.,;\s@\"]+(\.[^<>()\[\]\.,;\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,}))/', $html, $matches);

            // Check whether there are any matches
            if ($matches) {
                // Exlude http links
                if (strpos($matches[0], '://') !== false) {
                    return $matches[0];
                }

                // Check whether there are any 'mailto' links left
                if (strlen($matches[0]) > 0) {
                    // todo make sure this works properly in a report
                    return '<a class="dynamic-replacement email-marker" href="mailto:' . $matches[0] . '">' . $matches[0] . '</a>';
                }
            }
        }

        return $html;
    }

    /**
     * Replace placeholders
     *
     * !!! BE CAREFUL !!!
     *
     * This logic is used in object list
     *
     * @param $html
     *
     * @return string|string[]|null
     */
    public function replacePlaceholders($html)
    {
        global $g_dirs;

        $html = preg_replace([
            '/[\w\- ]+ \{1\}/', // Replace the "Title {1}" with the root location house.
            '/\{(#[0-9a-fA-F]{3,6})\}/', // Replace "{#123456}" with the cmdb-marker.
            '/([^\{\>,]+) \{([0-9]+)\}/' // Replace object links with format "Title {id}".
        ], [
            '<img class="vam" src="' . $g_dirs['images'] . 'icons/silk/house.png">',
            '<div class="dynamic-replacement cmdb-marker" style="background-color: $1;"></div>',
            ' <a class="dynamic-replacement quickinfo" href="?objID=$2" data-object-id="$2">$1</a>'
        ], $html);

        // Replace email with mailto links
        if ($this->linkEmailAddresses) {
            $html = preg_replace('/((([^<>()\[\]\.,;\s@\"]+(\.[^<>()\[\]\.,;\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,}))/', function ($match) {
                /*
                 * ID-5939 Testing for "://" - in this case we got a link like "ssh://root@domain.tld" and NOT an email address.
                 * We CAN NOT replace "http://..." strings with HTML links or it will break all images etc.
                 */
                if (array_search('://', $match) > 0) {
                    return $match;
                }

                return '<a class="dynamic-replacement email-marker" href="mailto:' . $match . '">' . $match . '</a>';
            }, $html);
        }

        return $html;
    }
}
