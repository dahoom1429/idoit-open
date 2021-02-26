<?php

/**
 * i-doit
 *
 * Language manager used by the template library. It is responsible for managing the language caches.
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_language_manager extends isys_component
{
    /**
     * Properties.
     *
     * @var  array
     */
    private $m_langcache;

    /**
     * Variable which holds the loaded language.
     *
     * @var  string
     */
    private $m_language = 'en';

    /**
     *
     * @global  isys_component_database $g_comp_database_system
     * @return  array
     */
    public function fetch_available_languages()
    {
        global $g_comp_database_system;

        $l_return = [];
        $l_q = $g_comp_database_system->query("SELECT * FROM isys_language WHERE isys_language__available = 1 ORDER BY isys_language__sort ASC;");

        while ($l_lang = $g_comp_database_system->fetch_row_assoc($l_q)) {
            $l_return[] = $l_lang;
        }

        return $l_return;
    }

    /**
     * @param $p_ident
     *
     * @author Dennis Stücken <dstuecken@i-doit.com>
     * @return mixed|string
     */
    public function get_in_text($p_ident)
    {
        if (strpos($p_ident, 'LC_') !== false) {
            // Replaces all languange constant inside a textstring.
            return preg_replace_callback("/(LC_[A-Za-z\_0-9]+)/", function ($matches) use ($p_ident) {
                if (count($matches)) {
                    $languageKey = current($matches);

                    // @see  ID-5550  When the constant is set use it - even if it's "0".
                    return (isset($this->m_langcache[$languageKey])
                        ? $this->m_langcache[$languageKey]
                        : $languageKey);
                }

                return $p_ident;
            }, $p_ident);
        }

        return $this->get($p_ident);
    }

    /**
     * Returns the language string specified by the language identifier ($p_ident) and an optional array for substituting parameters ($p_subst_array).
     *
     * @param   string $p_ident
     * @param   array  $p_subst_array
     *
     * @return  string
     * @author  André Wösten
     */
    public function get($p_ident, $p_subst_array = null)
    {
        if (!empty($p_ident) && isset($this->m_langcache[$p_ident])) {
            if (is_array($p_subst_array)) {
                $l_retcode = vsprintf($this->m_langcache[$p_ident], $p_subst_array);
            } else {
                if ($p_subst_array !== null) {
                    $l_args = func_get_args(); // phpcs:ignore
                    unset($l_args[0]);
                    $l_retcode = vsprintf($this->m_langcache[$p_ident], $l_args);
                } else {
                    $l_retcode = $this->m_langcache[$p_ident];
                }
            }
        } else {
            // If the Language constant is not defined, directly output it, so you know, what is missing.
            return $p_ident;
        }

        /*
         * Match again for language constants in evaluated language strings and replace them. We are not using the iterative variant here (e.g. enumerating
         * through the array with language constants) - instead we're matching directly for existing language constants and replace them (using substr_replace, which
         * is faster than preg_replace in our case). If a Language constant has more than one language constants in it, we have to recalculate the substition offsets.
         */
        if ($p_subst_array !== null && strpos($l_retcode, '[{') !== false) {
            if (preg_match_all("/\[\{(.*?)\}\]/i", $l_retcode, $l_regex, PREG_OFFSET_CAPTURE)) {
                $l_d_offset = 0;

                $count = is_countable($l_regex[0]) && count($l_regex[0]);
                for ($l_i = 0;$l_i < $count;$l_i++) {
                    /*
                     * If using PREG_OFFSET_CAPTURE with preg_match_all, we get a 3-dimensional array:
                     *  1. Dimension: 0 = Original data, 1 = First regex-group, 2 = Second regex-group and so on
                     *  2. Dimension: Index of search result
                     *  3. Dimension: 0 = Data, 1 = Offset
                     */
                    $l_source = $l_regex[0][$l_i][0];
                    $l_const = $l_regex[1][$l_i][0];
                    $l_offset = $l_regex[0][$l_i][1];

                    // This is necessary since we don't want a recursive loop.
                    if ($l_const != $p_ident) {
                        // Fetch data for language constant.
                        $l_newdata = $this->get($l_const);

                        if (is_array($p_subst_array)) {
                            if (array_key_exists($l_const, $p_subst_array)) {
                                $l_newdata = $this->get($p_subst_array["$l_const"]);
                            }
                        }

                        // Recalculate substition offsets.
                        $l_offset -= $l_d_offset;
                        $l_d_offset += (strlen($l_source) - strlen($l_newdata));

                        // Do substitution.
                        $l_retcode = substr_replace($l_retcode, $l_newdata, $l_offset, strlen($l_source));
                    }
                }
            }
        }

        return $l_retcode;
    }

    /**
     * Retrieves the currently loaded language as string (for example "de" or "en").
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_loaded_language()
    {
        return $this->m_language;
    }

    /**
     * Magic method wrapper for get().
     *
     * @param   string $p_ident
     *
     * @return  string
     * @uses    isys_component_template_language_manager::get()
     */
    public function __get($p_ident)
    {
        return $this->get($p_ident);
    }

    /**
     * Returns a reference to the language cache.
     *
     * @return  array
     */
    public function &get_cache()
    {
        return $this->m_langcache;
    }

    /**
     * Loads and creates, if necessary, the language cache into self::$m_langcache.
     *
     * @param   string $p_language_short
     *
     * @throws  Exception
     * @return  boolean
     */
    public function load($p_language_short = null)
    {
        global $g_absdir;

        if ($p_language_short !== null) {
            $this->m_language = str_replace(chr(0), '', $p_language_short);
        }
        $this->m_langcache = [];
        $mainFolder = $g_absdir . '/src/lang';
        $this->load_folder($mainFolder);
        foreach (glob("$g_absdir/src/classes/modules/*/lang") as $folder) {
            $this->load_folder($folder);
        }
        $this->load_custom($this->m_language, $mainFolder);
        return true;
    }

    /**
     * Load the language from the folder
     * @param $folder
     *
     * @return bool
     * @throws Exception
     */
    public function load_folder($folder)
    {
        $file = $folder . "/" . $this->m_language . ".inc.php";

        if (!file_exists($file) || strstr($this->m_language, "/")) {
            $file = $folder . "/en.inc.php";
        }
        $this->append_lang_file($file);
        return true;
    }

    /**
     * Loads the custom language file, if available.
     *
     * @param string $p_language_short
     * @param null    $folder
     */
    public function load_custom($p_language_short = null, $folder = null)
    {
        global $g_absdir;

        if ($p_language_short !== null) {
            $this->m_language = str_replace(chr(0), '', $p_language_short);
        }
        if (is_null($folder)) {
            $folder = $g_absdir . '/src/lang';
        }

        $file = $folder . '/' . $this->m_language . '_custom.inc.php';
        $this->append_lang_file($file);
    }

    /**
     * Method for generically adding new translations.
     *
     * @param   array $p_language_array
     *
     * @return  isys_component_template_language_manager
     */
    public function append_lang(array $p_language_array = [])
    {
        if (is_array($p_language_array)) {
            if (!is_array($this->m_langcache)) {
                $this->m_langcache = $p_language_array;
            } else {
                $this->m_langcache = array_merge($this->m_langcache, $p_language_array);
            }
        }

        return $this;
    }

    /**
     * Method for generically adding new translations.
     *
     * @param   string $p_language_file
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @return  isys_component_template_language_manager
     */
    public function append_lang_file($p_language_file)
    {
        global $g_langcache;

        if (file_exists($p_language_file)) {
            $g_langcache = [];
            // Aufgrund von RT#27300 verwenden wir kein include_once.
            $l_lang = include $p_language_file;

            if (is_array($l_lang)) {
                return $this->append_lang($l_lang);
            } elseif (isset($g_langcache) && is_countable($g_langcache) && count($g_langcache) > 0) {
                return $this->append_lang($g_langcache);
            }

            unset($l_lang);
        }

        return $this;
    }

    /**
     * Calls load with $p_language_id as language identifier.
     *
     * @param  string $p_language_short
     */
    public function __construct($p_language_short)
    {
        $this->m_langcache = [];

        $this->load($p_language_short);
    }
}
