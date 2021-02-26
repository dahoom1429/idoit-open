<?php

use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\RequirementPasswordGenerator;

/**
 * AJAX.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_password extends isys_ajax_handler
{
    /**
     * Init method for this AJAX request.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $response = [
            'success' => true,
            'data'    => null,
            'message' => ''
        ];

        try {
            switch ($_GET['strength']) {
                default:
                case 'weak':
                    $response['data'] = $this->generateWeakPassword();
                    break;

                case 'medium':
                    $response['data'] = $this->generateMediumPassword();
                    break;

                case 'strong':
                    $response['data'] = $this->generateStrongPassword();
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage() . ' (' . get_class($e) . ')';
        }

        echo isys_format_json::encode($response);

        $this->_die();
    }

    /**
     * Generates a weak password (using 8 lowercase characters and numbers only).
     *
     * @return string
     */
    private function generateWeakPassword()
    {
        return (new ComputerPasswordGenerator())
            ->setLowercase(true)
            ->setUppercase(false)
            ->setNumbers(true)
            ->setAvoidSimilar(true)
            ->setLength(8)
            ->generatePassword();
    }

    /**
     * Generates a medium password (12 characters, digits and some symbols).
     *
     * @return string
     */
    private function generateMediumPassword()
    {
        return (new ComputerPasswordGenerator())
            ->setLowercase(true)
            ->setUppercase(true)
            ->setNumbers(true)
            ->setSymbols(true)
            ->setAvoidSimilar(true)
            ->setLength(12)
            ->generatePassword();
    }

    /**
     * Generates a strong password (16 characters, digits and symbols).
     *
     * @return string
     * @throws \Hackzilla\PasswordGenerator\Exception\CharactersNotFoundException
     * @throws \Hackzilla\PasswordGenerator\Exception\ImpossibleMinMaxLimitsException
     */
    private function generateStrongPassword()
    {
        return (new RequirementPasswordGenerator())
            ->setOptionValue(RequirementPasswordGenerator::OPTION_UPPER_CASE, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_LOWER_CASE, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_NUMBERS, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_SYMBOLS, true)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_UPPER_CASE, 2)
            ->setMaximumCount(RequirementPasswordGenerator::OPTION_UPPER_CASE, 8)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_LOWER_CASE, 2)
            ->setMaximumCount(RequirementPasswordGenerator::OPTION_LOWER_CASE, 8)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_NUMBERS, 4)
            ->setMaximumCount(RequirementPasswordGenerator::OPTION_NUMBERS, 8)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_SYMBOLS, 6)
            ->setMaximumCount(RequirementPasswordGenerator::OPTION_SYMBOLS, 8)
            ->setAvoidSimilar(false)
            ->setLength(16)
            ->generatePassword();
    }
}