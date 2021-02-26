<?php

/**
 * Event Manager
 *
 * Executes actions triggered by CMDB-Events like creating objects.
 * Currently only generates logbook entries for the defined events, but more
 * actions are possible.
 *
 * SINGLETON
 *
 * @package     i-doit
 * @subpackage  Events
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_event_manager
{
    private static $alertLevelsInited = false;
    /**
     * Alert level mapping.
     *
     * @var  array
     */
    protected static $m_alertLevels = [];

    /**
     * Variable, holding the singleton instance.
     *
     * @static
     * @var  isys_event_manager
     */
    private static $m_instance = null;

    /**
     * isys_import__id
     *
     * @var int
     */
    private $m_import_id = null; // function

    /**
     * Method for retrieving the singleton instance.
     *
     * @static
     * @return  isys_event_manager
     */
    public static function getInstance()
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self();
        }

        return self::$m_instance;
    }

    /**
     * Private clone method for providing the singleton pattern.
     */
    public function __clone()
    {
    }

    /**
     * @param $p_import_id
     */
    public function set_import_id($p_import_id)
    {
        $this->m_import_id = $p_import_id;
    }

    /**
     * Gets current import id
     *
     * @return int
     */
    public function get_import_id()
    {
        return $this->m_import_id;
    }

    /**
     * Trigger general event.
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_date
     * @param   integer $p_alertLevel
     * @param   string  $p_source
     * @param   integer $p_objID
     * @param   string  $p_changes
     * @param   string  $p_comment
     *
     * @return  boolean
     */
    public function triggerEvent($p_title, $p_description, $p_date = null, $p_alertLevel, $p_source, $p_objID = null, $p_changes = null, $p_comment = null)
    {
        global $g_comp_database;

        $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);

        if ($p_date == null) {
            $p_date = isys_glob_datetime();
        }

        return $l_daoLogbook->set_entry($p_title, $p_description, $p_date, $p_alertLevel, $p_objID, null, null, null, $p_source, $p_changes, $p_comment);
    }

    /**
     * Initialize static variables
     */
    private static function initAlertLevels()
    {
        if (self::$alertLevelsInited) {
            return;
        }
        self::$alertLevelsInited = true;
        self::$m_alertLevels = filter_array_by_value_of_defined_constants([
            'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED'              => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED__NOT'         => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_DELETED'               => 'C__LOGBOOK__ALERT_LEVEL__2',
            'C__LOGBOOK_EVENT__CATEGORY_DELETED__NOT'          => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_PURGED'                => 'C__LOGBOOK__ALERT_LEVEL__3',
            'C__LOGBOOK_EVENT__CATEGORY_PURGED__NOT'           => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED'               => 'C__LOGBOOK__ALERT_LEVEL__0',
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT'          => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_RECYCLED'              => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__CATEGORY_RECYCLED__NOT'         => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_CREATED'                 => 'C__LOGBOOK__ALERT_LEVEL__0',
            'C__LOGBOOK_EVENT__OBJECT_CREATED__NOT'            => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_CHANGED'                 => 'C__LOGBOOK__ALERT_LEVEL__0',
            'C__LOGBOOK_EVENT__OBJECT_CHANGED__NOT'            => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_ARCHIVED'                => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_ARCHIVED__NOT'           => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_DELETED'                 => 'C__LOGBOOK__ALERT_LEVEL__2',
            'C__LOGBOOK_EVENT__OBJECT_DELETED__NOT'            => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_PURGED'                  => 'C__LOGBOOK__ALERT_LEVEL__3',
            'C__LOGBOOK_EVENT__OBJECT_PURGED__NOT'             => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_RECYCLED'                => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECT_RECYCLED__NOT'           => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__POBJECT_MALE_PLUG_CREATED__NOT' => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED'             => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED__NOT'        => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED'             => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED__NOT'        => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED'            => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED__NOT'       => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED'             => 'C__LOGBOOK__ALERT_LEVEL__2',
            'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED__NOT'        => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED'              => 'C__LOGBOOK__ALERT_LEVEL__3',
            'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED__NOT'         => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED'            => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED__NOT'       => 'C__LOGBOOK__ALERT_LEVEL__1',
            'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED'               => 'C__LOGBOOK__ALERT_LEVEL__1'
        ]);
    }

    /**
     * Manages an event affecting the CMDB by creating an entry in the logbook.
     *
     * @param   string  $p_strConstEvent The event constant
     * @param   string  $p_strDesc       The description of the logbook entry to create
     * @param   integer $p_nObjID        The ID of the affected object, if applicable
     * @param   integer $p_nObjTypeID    The ID of the affected object type, if applicable
     * @param   string  $p_category
     * @param   string  $p_changes
     * @param   string  $p_comment
     * @param   integer $p_reasonID
     * @param   string  $p_entry_identifier
     *
     * @return  boolean
     */
    public function triggerCMDBEvent(
        $p_strConstEvent,
        $p_strDesc,
        $p_nObjID = null,
        $p_nObjTypeID = null,
        $p_category = null,
        $p_changes = null,
        $p_comment = null,
        $p_reasonID = null,
        $p_object_title_static = null,
        $p_entry_identifier = null,
        $p_count_changes = 0,
        $p_source = null
    ) {
        if ($p_source === null && defined('C__LOGBOOK_SOURCE__INTERNAL')) {
            C__LOGBOOK_SOURCE__INTERNAL;
        }
        global $g_comp_database;

        if (!$p_object_title_static) {
            if ($p_nObjID) {
                /** @var $l_dao isys_cmdb_dao */
                $l_strObjName = isys_cmdb_dao::instance($g_comp_database)
                    ->get_obj_name_by_id_as_string($p_nObjID);
            } elseif ($p_strConstEvent == 'C__LOGBOOK_EVENT__OBJECT_PURGED' && $p_category !== '') {
                $l_strObjName = $p_category;
                $p_category = null;
            } else {
                $l_strObjName = '';
            }
        } else {
            $l_strObjName = $p_object_title_static;
        }

        if ($p_nObjTypeID) {
            $l_strObjTypeTitle = isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_name_by_id_as_string($p_nObjTypeID);
        } else {
            $l_strObjTypeTitle = '';
        }

        /** @var $l_daoLogbook isys_component_dao_logbook */
        $l_daoLogbook = isys_component_dao_logbook::instance($g_comp_database);

        self::initAlertLevels();
        $l_alertlevel = (self::$m_alertLevels[$p_strConstEvent]) ? self::$m_alertLevels[$p_strConstEvent] : defined_or_default('C__LOGBOOK__ALERT_LEVEL__0');

        // Set entry in the logbook.
        return $l_daoLogbook->set_entry(
            $p_strConstEvent,
            $p_strDesc,
            isys_glob_datetime(),
            $l_alertlevel,
            $p_nObjID,
            $l_strObjName,
            $l_strObjTypeTitle,
            $p_category,
            $p_source,
            $p_changes,
            $p_comment,
            $p_reasonID,
            $p_entry_identifier,
            $p_count_changes
        );
    }

    /**
     * Manages an event affecting the import by creating an entry in the logbook
     *
     * @param      $p_strConstEvent
     * @param      $p_strDesc
     * @param      $p_import_id
     * @param null $p_nObjID
     * @param null $p_nObjTypeID
     * @param null $p_category
     * @param null $p_changes
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function triggerImportEvent(
        $p_strConstEvent,
        $p_strDesc,
        $p_nObjID = null,
        $p_nObjTypeID = null,
        $p_category = null,
        $p_changes = null,
        $p_comment = null,
        $p_reasonID = null,
        $p_object_title_static = null,
        $p_import_id = null,
        $p_count_changes = 0,
        $p_source = null
    ) {
        if ($p_source === null && defined('C__LOGBOOK_SOURCE__IMPORT')) {
            $p_source = C__LOGBOOK_SOURCE__IMPORT;
        }
        if ($this->triggerCMDBEvent(
            $p_strConstEvent,
            $p_strDesc,
            $p_nObjID,
            $p_nObjTypeID,
            $p_category,
            $p_changes,
            $p_comment,
            $p_reasonID,
            $p_object_title_static,
            null,
            $p_count_changes,
            $p_source
        )) {
            if (!$p_import_id) {
                $p_import_id = $this->m_import_id;
            }

            if ($p_import_id) {
                global $g_comp_database;
                isys_component_dao_logbook::instance($g_comp_database)
                    ->set_import_entry($p_import_id);
            }
        }
    }

    /**
     * Method for translating the current event.
     *
     * @param   string $p_strEvent
     * @param   string $p_name
     * @param   string $p_category
     * @param   string $p_objType
     * @param   string $p_entry_identifier
     * @param    int   $p_changed_entries
     *
     * @return  string
     */
    public function translateEvent($p_strEvent, $p_name, $p_category, $p_objType, $p_entry_identifier = null, $p_changed_entries = 0)
    {
        $languageManager = isys_application::instance()->container->get('language');

        $l_entry_lc = 'LC__LOGBOOK__CATEGORY_ENTRY';
        if ($p_changed_entries > 1) {
            $l_entry_lc = sprintf(isys_application::instance()->container->get('language')
                ->get('LC__LOGBOOK__CATEGORY_ENTRIES'), $p_changed_entries);
        }
        if (isset($p_entry_identifier) && !empty($p_entry_identifier)) {
            $l_entry_lc = 'LC__LOGBOOK__SPECIFIC_CATEGORY_ENTRY';
        }

        switch ($p_strEvent) {
            case 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': "' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' "' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_DELETED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': "' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' "' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__OBJECT_DELETED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_PURGED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': "' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' "' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_CHANGED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__CATEGORY_UPDATED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__CATEGORY_UPDATED__NOT');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': "' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get($l_entry_lc, [$p_entry_identifier]) . ' ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' "' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__OBJECT_RECYCLED');
                break;

            case 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__RELATION_CREATED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__RELATION') . ' ' . '"' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__RELATION_CREATED');
                break;

            case 'C__LOGBOOK_EVENT__RELATION_DELETED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__RELATION') . ' ' . '"' .
                    $languageManager->get($p_category) . '" ' . $languageManager->get('LC__LOGBOOK__RELATION_DELETED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CREATED':
                return $languageManager->get('LC__CMDB__CATG__ODEP_OBJ') . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' .
                    $languageManager->get($p_objType) . '") ' . $languageManager->get('LC__LOGBOOK__OBJECT_CREATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CHANGED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '") ' .
                    ((strlen($p_category) > 0) ? (':' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' . $languageManager->get($p_category) . '" ') : '') .
                    $languageManager->get('LC__LOGBOOK__CATEGORY_UPDATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_CHANGED__NOT':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' . $languageManager->get($p_category) . '" ' .
                    $languageManager->get('LC__LOGBOOK__CATEGORY_UPDATED__NOT');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_DELETED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_DELETED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_PURGED':
                return $p_category . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_RECYCLED':
                return $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' . $languageManager->get($p_objType) . '"): ' .
                    $languageManager->get('LC__CMDB__CATG__ODEP_OBJ') . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_RECYCLED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECT_RECYCLED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__POBJECT_MALE_PLUG_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED':
                return $languageManager->get('LC__CMDB__OBJTYPE') . ' ' . $languageManager->get($p_objType) . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_CREATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED':
                return $languageManager->get('LC__CMDB__OBJTYPE') . ' ' . $languageManager->get($p_objType) . ' ' . $languageManager->get('LC__LOGBOOK__CATEGORY_UPDATED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED':
                return $languageManager->get('LC__CMDB__OBJTYPE') . ' ' . $languageManager->get($p_objType) . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_ARCHIVED');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_ARCHIVED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_DELETED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED':
                return $languageManager->get('LC__CMDB__OBJTYPE') . ' ' . $p_category . ' ' . $languageManager->get('LC__LOGBOOK__OBJECT_DELETED_PERMANENTLY');
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_PURGED__NOT':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED':
                break;

            case 'C__LOGBOOK_EVENT__OBJECTTYPE_RECYCLED__NOT':
                break;
            case 'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED':
                return isys_application::instance()->container->get('language')
                    ->get('LC__LOGBOOK__TEMPLATE_HAS_BEEN_APPLIED', $p_category);
                break;

            case 'C__LOGBOOK_ENTRY__MASS_CHANGE_APPLIED':
                return $languageManager->get('LC__LOGBOOK__MASS_CHANGES_FOR_OBJECT') . ' ' . $p_name . ' (' . $languageManager->get('LC__CMDB__CATG__TYPE') . ': ' . '"' .
                    $languageManager->get($p_objType) . '"): ' . 'In ' . $languageManager->get('LC__CMDB__CATG__CATEGORY') . ' ' . '"' . $languageManager->get($p_category) .
                    '" ' . $languageManager->get('LC__LOGBOOK__HAS_BEEN_APPLIED');
                break;

            default:
                return $p_strEvent;
                break;
        }
    }

    /**
     * Private constructor for providing the singleton pattern.
     */
    protected function __construct()
    {
    }
}
