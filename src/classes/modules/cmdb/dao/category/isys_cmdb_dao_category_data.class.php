<?php

/**
 * i-doit category data
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_data extends isys_array
{
    /**
     * @var  isys_cmdb_dao_category_data[]
     */
    protected static $m_instances = [];

    /**
     * @var array
     */
    protected static $m_categoryBlacklist = [];

    /**
     * Reference to category DAO.
     *
     * @var  isys_cmdb_dao_category
     */
    protected $m_category_dao = null;

    /**
     * @var  integer
     */
    private $m_object_id = null;

    /**
     * @var  integer
     */
    private $m_object_type_id = null;

    /**
     * Return property values only if theses flags are set to TRUE. Set to empty array to return all properties.
     *
     * @var array
     */
    private $m_provides_flags = [];

    /**
     * @return isys_cmdb_dao_category_data
     */
    public static function factory()
    {
        return new isys_cmdb_dao_category_data();
    }

    /**
     * Get category dao by id
     *
     * @param int $p_object_id
     * @param int $p_category_id
     * @param int $p_category_type
     *
     * @return isys_cmdb_dao_category
     */
    final public static function dao($p_object_id, $p_category_id, $p_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {
        global $g_comp_database;

        return isys_factory_cmdb_category_dao::get_instance_by_id($p_category_type, $p_category_id, $g_comp_database)
            ->set_object_id($p_object_id);
    }

    /**
     * @param $p_object_id
     */
    public static function free($p_object_id)
    {
        unset(static::$m_instances[$p_object_id]);
    }

    /**
     * @param       $p_object_id
     * @param array $p_provides_flags Return property values only if theses flags are set to TRUE. Set to empty array to return all properties.
     *
     * @return isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    final public static function initialize($p_object_id, $p_provides_flags = [])
    {
        $database = isys_application::instance()->container->get('database');

        // Prepare singleton instance for this object.
        if (!isset(static::$m_instances[$p_object_id])) {
            /* @var  $l_dao  isys_cmdb_dao_object_type */
            $l_dao = isys_cmdb_dao_object_type::instance($database);

            // Get object type id.
            $l_object_type_id = $l_dao->get_objTypeID($p_object_id);

            // Initialize category data instance.
            static::$m_instances[$p_object_id] = self::create_instance($p_object_id, $l_object_type_id, $p_provides_flags);

            // Retrieve all categories assigned to this object type.
            $l_category_types = $l_dao->get_categories($l_object_type_id, [
                C__CMDB__CATEGORY__TYPE_GLOBAL,
                C__CMDB__CATEGORY__TYPE_SPECIFIC,
                C__CMDB__CATEGORY__TYPE_CUSTOM
            ], 'const');

            if (is_countable($l_category_types) && count($l_category_types) > 0) {
                foreach ($l_category_types as $l_categories) {
                    foreach ($l_categories as $l_catdata) {
                        // If a blacklist is set than we only want category data which are not on the blacklist
                        if (class_exists($l_catdata['class_name']) && !isset(self::$m_categoryBlacklist[$l_catdata['const']])) {
                            if ($l_catdata['class_name'] == 'isys_cmdb_dao_category_g_custom_fields') {
                                /**
                                 * We cant use the factory, because the custom ID might differ.
                                 *
                                 * @var $l_dao isys_cmdb_dao_category_g_custom_fields
                                 */
                                $l_dao = new $l_catdata['class_name']($database);
                                $l_dao->set_catg_custom_id($l_catdata['id'])
                                    ->set_object_id($p_object_id);
                            } else {
                                $l_dao = call_user_func([
                                    $l_catdata['class_name'],
                                    'instance'
                                ], $database)->set_object_id($p_object_id);
                            }

                            /* @var  isys_cmdb_dao_category $l_dao */
                            static::$m_instances[$p_object_id][$l_catdata['const']] = self::create_instance($p_object_id, $l_object_type_id, $p_provides_flags)
                                ->set_dao($l_dao);
                        }
                    }
                }
            }

            unset($l_category_types);
        }

        return static::$m_instances[$p_object_id];
    }

    /**
     * @param $p_object_id
     * @param $p_object_type_id
     * @param $p_provides_flags
     *
     * @return isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    private static function create_instance($p_object_id, $p_object_type_id, $p_provides_flags)
    {
        return isys_cmdb_dao_category_data::factory()
            ->set_provides_flags($p_provides_flags)
            ->set_object_id($p_object_id)
            ->set_object_type_id($p_object_type_id);
    }

    /**
     * @param array $p_flags
     *
     * @inherit
     *
     * @return isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    public function set_provides_flags(array $p_flags)
    {
        $this->m_provides_flags = $p_flags;

        return $this;
    }

    /**
     * @param   integer $p_object_id
     *
     * @return  isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    public function set_object_id($p_object_id)
    {
        $this->m_object_id = $p_object_id;

        return $this;
    }

    /**
     * @param   integer $p_object_type_id
     *
     * @return  isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    public function set_object_type_id($p_object_type_id)
    {
        $this->m_object_type_id = $p_object_type_id;

        return $this;
    }

    /**
     * Set current category dao
     *
     * @param isys_cmdb_dao_category $p_dao
     *
     * @return isys_cmdb_dao_category_data|isys_cmdb_dao_category_data[]
     */
    public function set_dao(isys_cmdb_dao_category $p_dao)
    {
        $this->m_category_dao = $p_dao;

        return $this;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function get_dao()
    {
        return $this->m_category_dao;
    }

    /**
     * Convert category data to CSV
     *
     * @param string $p_separator
     * @param bool   $p_include_headers
     *
     * @return string
     */
    public function toCSV($p_separator = ',', $p_include_headers = true)
    {
        $this->data()
            ->rewind();

        $l_content = $l_headers = '';

        $l_categoryData = $this->data();

        if ($p_include_headers) {
            foreach ($l_categoryData->current() as $l_key => $l_value) {
                $l_headers .= $l_key . $p_separator;
            }
            $l_headers = rtrim($l_headers, $p_separator);
            $l_content .= implode($p_separator, iterator_to_array($l_categoryData->current())) . "\n";
            $l_categoryData->next();
        }

        /**
         * Iterate through content
         */
        while ($l_categoryData->valid()) {
            $l_array = $l_categoryData->current();
            if ($l_array) {
                $l_content .= implode($p_separator, iterator_to_array($l_array)) . "\n";
            }
            $l_categoryData->next();
        }

        return $l_headers . "\n" . $l_content;
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * ------------------------------------------------------------------------------------------
     */

    /**
     * @return $this
     */
    public function jsonSerialize()
    {
        return (array)$this->reload();
    }

    /**
     * (Re)loads category data
     *
     * @return $this
     */
    public function reload()
    {
        if ($this->m_category_dao instanceof isys_cmdb_dao_category) {
            $this->m_category_dao->category_data($this, C__RECORD_STATUS__NORMAL, $this->m_provides_flags);
        }

        return $this;
    }

    /**
     *
     * @param   string $p_category_const
     *
     * @return  isys_cmdb_dao_category_data
     */
    public function data($p_category_const = null)
    {
        if ($p_category_const && isset($this[$p_category_const])) {
            if ($this[$p_category_const] instanceof isys_cmdb_dao_category_data) {
                return $this[$p_category_const]->data();
            }
        }

        if (count($this) === 0) {
            $this->reload();
        }

        return $this;
    }

    /**
     * Overload default path parameter with a new instance of isys_cmdb_dao_category_data
     *
     * @param string $p_path
     * @param null   $p_default
     *
     * @return isys_cmdb_dao_category_data
     */
    public function path($p_path, $p_default = null)
    {
        return parent::path($p_path, $p_default);
    }

    /**
     * Returns the primary index of this dataset
     *
     * @return int
     */
    public function primaryIndex()
    {
        $yesTranslated = isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__YES');

        foreach ($this as $entryId => $content) {
            if (!$content->has('primary')) {
                return key($this);
            }

            $primaryFieldValue = (string)$content->get('primary');
            if ((is_numeric($primaryFieldValue) && (int)$primaryFieldValue === 1) || $primaryFieldValue === $yesTranslated) {
                return $entryId;
            }
        }
        return key($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $that = $this;
        array_walk($this, function ($val, $key) use ($that) {
            if ($val instanceof isys_cmdb_dao_category_data) {
                $that[$key] = $val->data()
                    ->toArray();
            }
        });

        return (array)$this->data();
    }

    /**
     * Free memory
     */
    public function __destruct()
    {
        unset($this->m_category_dao, $this->m_object_id, $this->m_provides_flags, $this->m_object_type_id);
    }

    /**
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function __get($p_key)
    {
        return $this->data()
            ->current()->$p_key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->data()
            ->current();
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function getCategoryBlacklist()
    {
        return self::$m_categoryBlacklist;
    }

    /**
     * Set Blacklist so that the initialization only handles categories which are not on the blacklist
     *
     * @param array $m_categoryBlacklist ['C__CATG__LOGBOOK' => 22, ...]
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function setCategoryBlacklist($p_categoryBlacklist)
    {
        self::$m_categoryBlacklist = $p_categoryBlacklist;
    }
}
