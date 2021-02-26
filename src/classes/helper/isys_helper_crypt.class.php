<?php

use phpseclib\Crypt;

/**
 * i-doit
 *
 * Helper methods for Crypting via phpseclib
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Kevin Mauel <kmauel@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_helper_crypt
{
    /**
     * Default delimiter
     *
     * @type string
     */
    const DELIMITER = '|$$|';

    /**
     * Encrypt a string.
     *
     * @param  string $string
     *
     * @return string
     * @author Kevin Mauel <kmauel@i-doit.org>
     */
    public static function encrypt($string)
    {
        global $g_crypto_hash;

        $cipher = new Crypt\Rijndael();
        $cipher->setKey($g_crypto_hash);
        $l_iv = Crypt\Random::string($cipher->getBlockLength() >> 3);
        $cipher->setIV($l_iv);

        $l_crypted_string = $l_iv . self::DELIMITER . $cipher->encrypt($string);

        return base64_encode($l_crypted_string);
    }

    /**
     * Decrypt a string.
     *
     * @param  string $string
     *
     * @return string|boolean
     * @author Kevin Mauel <kmauel@i-doit.org>
     */
    public static function decrypt($string)
    {
        global $g_crypto_hash;

        // Decrypted password
        $decryptedPassword = false;

        // Check whether string is really a string
        if (is_string($string)) {
            // Prepare encrypting password
            $base64decoding = base64_decode($string, true);

            // Check whether delimiter is included
            if (strpos($base64decoding, self::DELIMITER) !== false) {
                // Destruct crypted string into its parts
                $cryptedArray = explode(self::DELIMITER, $base64decoding);

                // Prepare and run decrypting routine
                $cipher = new Crypt\Rijndael();
                $cipher->setKey($g_crypto_hash);
                $cipher->setIV($cryptedArray[0]);

                // Decrypt password
                $password = $cipher->decrypt($cryptedArray[1]);

                // Validate decrypted password
                if (self::validateDecryption($password)) {
                    $decryptedPassword = $password;
                }
            }
        }

        return $decryptedPassword;
    }

    /**
     * Validate decrypted password
     *
     * @param string $string
     *
     * @return bool
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function validateDecryption($string)
    {
        // Needed because of some possible inconsistencies in libiconv
        ini_set('mbstring.substitute_character', 'none');

        // Convert UTF-8 to UTF-8//Ignore - Ignore unknown characters
        $stringWithoutUnknownChars = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        // Check whether string length of target and source are the same
        if (strlen($stringWithoutUnknownChars) == strlen($string)) {
            return true;
        }

        return false;
    }
}
