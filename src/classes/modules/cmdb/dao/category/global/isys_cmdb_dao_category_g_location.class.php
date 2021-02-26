<?php

use idoit\Component\Location\Coordinate;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Context\Context;

/**
 * i-doit
 *
 * DAO: global category for locations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_location extends isys_cmdb_dao_category_global
{
    /**
     * Location cache (including parent objects).
     *
     * @var  array
     */
    protected static $m_location_cache = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'location';

    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_location_list__parentid';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_location_list__isys_obj__id';

    /**
     * @var []
     */
    protected static $invalidateCache = [];

    /**
     * Static method for checking if a given slot is free.
     *
     * @static
     *
     * @param   array   $p_used_slots
     * @param   integer $p_slot
     * @param   integer $p_insertion
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected static function slot_available($p_used_slots, $p_slot, $p_insertion)
    {
        if ($p_insertion == C__INSERTION__BOTH &&
            (in_array($p_slot . '-' . C__INSERTION__FRONT, $p_used_slots) || in_array($p_slot . '-' . C__INSERTION__REAR, $p_used_slots))) {
            return false;
        }

        if (in_array($p_slot . '-' . $p_insertion, $p_used_slots) || in_array($p_slot . '-' . C__INSERTION__BOTH, $p_used_slots)) {
            return false;
        }

        return true;
    }

    /**
     * Export Helper for property longitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_longitude($p_value, $p_row)
    {
        return $p_row['longitude'] ?: '';
    }

    /**
     * Export Helper for property latitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_latitude($p_value, $p_row = [])
    {
        return $p_row['latitude'] ?: '';
    }

    /**
     * Return complete location path.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_location_path($p_row)
    {
        global $g_dirs;

        if (!isset($p_row['isys_catg_location_list__parentid'])) {
            $l_parentid = $this->get_data(null, $p_row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        } else {
            $l_parentid = $p_row['isys_catg_location_list__parentid'];
        }

        if ($l_parentid > 0 && defined('C__OBJ__ROOT_LOCATION')) {
            if (!isset(self::$m_location_cache[C__OBJ__ROOT_LOCATION])) {
                self::$m_location_cache[C__OBJ__ROOT_LOCATION] = [
                    'title'  => '<img src="' . $g_dirs['images'] . 'icons/silk/house.png" class="vam" title="' . isys_application::instance()->container->get('language')
                            ->get('LC__OBJ__ROOT_LOCATION') . '" />',
                    'parent' => null
                ];

                // If the direct parent is not the root location, we display an arrow (just like between the other locations).
                if ($l_parentid != C__OBJ__ROOT_LOCATION) {
                    self::$m_location_cache[C__OBJ__ROOT_LOCATION]['title'] .= isys_tenantsettings::get('gui.separator.location', ' > ');
                }
            }

            return isys_popup_browser_location::instance()
                ->set_format_exclude_self(false)
                ->set_format_prefix(self::$m_location_cache[C__OBJ__ROOT_LOCATION]['title'] . ' > ')// Fixing ID-2937
                ->format_selection($l_parentid);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Return complete location path without html.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_location_path_raw($p_row)
    {
        try {
            /**
             * This will call default location path callback
             * and replace all html tags first and script tags
             * afterwards
             *
             * @see ID-5691
             */

            // Get title of root location object
            $rootLocationTitle = isys_application::instance()->container->get('language')->get('LC__OBJ__ROOT_LOCATION');

            // Strip all tags
            return strip_tags(
                // Replace root location house image with simple text
                preg_replace(
                    '#<img(.*?)title="'.$rootLocationTitle.'"(.*?)/>#is',
                    $rootLocationTitle,
                    // Replace <script> tags
                    preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->dynamic_property_callback_location_path($p_row))
                )
            );
        } catch (Exception $e) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
    }

    /**
     * Return the single location parent of the given object.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_location($p_row)
    {
        if (!isset($p_row['isys_catg_location_list__parentid'])) {
            $l_parentid = $this->get_data(null, $p_row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        } else {
            $l_parentid = $p_row['isys_catg_location_list__parentid'];
        }

        if ($l_parentid > 0) {
            $l_location_row = $this->get_object_by_id($l_parentid)
                ->get_row();

            return isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info($l_location_row['isys_obj__id'], $l_location_row['isys_obj__title'], C__LINK__OBJECT);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Callback method for the assembly option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assembly_options(isys_request $p_request)
    {
        $language = isys_application::instance()->container->get('language');
        // Preparing the assembly-options (horizontal/vertical).
        $l_options = [
            C__RACK_INSERTION__HORIZONTAL => $language->get('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL')
        ];

        // Check if callback is coming from report @see ID-7701 and ID-7700
        if (Context::instance()->getContextTechnical() === Context::CONTEXT_REPORT_SELECTION ||
            Context::instance()->getContextTechnical() === Context::CONTEXT_REPORT_CONDITION) {
            return [
                C__RACK_INSERTION__HORIZONTAL => $language ->get('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL'),
                C__RACK_INSERTION__VERTICAL => $language->get('LC__CMDB__CATS__ENCLOSURE__VERTICAL'),
            ];
        }

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_request->get_row('isys_catg_location_list__parentid'))
                ->get_row();

            if ($l_rack['isys_cats_enclosure_list__vertical_slots_rear'] > 0 || $l_rack['isys_cats_enclosure_list__vertical_slots_front'] > 0) {
                $l_options[C__RACK_INSERTION__VERTICAL] = $language->get('LC__CMDB__CATS__ENCLOSURE__VERTICAL');
            }
        }

        return $l_options;
    }

    /**
     * Callback function for dynamic property _pos
     *
     * @param array $p_row
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function retrievePositionInRack(array $p_row)
    {
        $language = isys_application::instance()->container->get('language');
        if (isset($p_row['isys_catg_location_list__id'])) {
            $locationDao = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->database);

            $catDataCurrentObject = $locationDao->get_data($p_row['isys_catg_location_list__id'])
                ->get_row();
            $parentObjectId = $catDataCurrentObject['isys_catg_location_list__parentid'];
            $currentObjectId = $catDataCurrentObject['isys_catg_location_list__isys_obj__id'];
            $currentObjectPosition = $catDataCurrentObject['isys_catg_location_list__pos'];
            $enclosureSorting = 'desc';

            if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
                $enclosureSorting = isys_cmdb_dao_category_s_enclosure::instance(isys_application::instance()->database)
                    ->get_data(null, $parentObjectId)
                    ->get_row_value('isys_cats_enclosure_list__slot_sorting');
            }
        } else {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $daoFormFactor = isys_cmdb_dao_category_g_formfactor::instance(isys_application::instance()->database);
        $maxRackUnits = $daoFormFactor->get_rack_hu($parentObjectId);
        $deviceUnits = $daoFormFactor->get_rack_hu($currentObjectId);

        switch ($enclosureSorting) {
            case 'asc':
                $startPosition = $maxRackUnits - ($maxRackUnits - $currentObjectPosition);
                $endPosition = $maxRackUnits - ($maxRackUnits - $currentObjectPosition - ($deviceUnits - 1));

                break;
            case 'desc':
            default:
                $currentObjectPosition--;
                $startPosition = $maxRackUnits - $currentObjectPosition;
                $endPosition = $startPosition - ($deviceUnits - 1);

                break;
        }

        return ($startPosition > 0 && $endPosition > 0) ? $language->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $startPosition . ' - ' .
            $endPosition : isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Callback method for the position dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_pos(isys_request $p_request)
    {
        $l_return = [];

        $l_by_ajax = $p_request->get_data('ajax', false);

        if ($l_by_ajax) {
            $l_rack_obj_id = $p_request->get_object_id();
        } else {
            $l_catdata = $this->get_data($p_request->get_category_data_id())
                ->get_row();
            $l_rack_obj_id = $l_catdata['isys_catg_location_list__parentid'];
        }

        $l_dao_ff = new isys_cmdb_dao_category_g_formfactor($this->get_database_component());

        $l_max_rack_unit = $l_rack_units = $l_dao_ff->get_rack_hu($l_rack_obj_id);

        $l_dao_loc = new isys_cmdb_dao_category_g_location($this->get_database_component());
        $l_res = $l_dao_loc->get_data(null, null, 'AND isys_catg_location_list__parentid = ' . $l_dao_loc->convert_sql_id($l_rack_obj_id) .
            ' AND isys_catg_location_list__pos > 0 ORDER BY isys_catg_location_list__pos ASC');

        $l_rack = [];

        while ($l_rack_units > 0) {
            if (empty($l_row)) {
                $l_row = $l_res->get_row();
            }

            if ($l_rack_units == $l_max_rack_unit - $l_row['isys_catg_location_list__pos']) {
                $l_rack_length = $l_dao_ff->get_rack_hu($l_row['isys_catg_location_list__isys_obj__id']);
                $l_string = '';
                $l_insertion = null;

                switch ($l_row['isys_catg_location_list__insertion']) {
                    case C__INSERTION__REAR:
                        // assigned to back
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__BACKSIDE_OCCUPIED') . ')';
                        $l_insertion = C__INSERTION__REAR;
                        break;

                    case C__INSERTION__FRONT:
                        // assigned to front
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED') . ')';
                        $l_insertion = C__INSERTION__FRONT;
                        break;

                    case C__INSERTION__BOTH:
                        // On both sides
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED') . ')';
                        $l_insertion = C__INSERTION__BOTH;
                        break;
                }

                $l_start_from = $l_rack_units - 1;
                $l_index_start = $l_row['isys_catg_location_list__pos'] + 1;
                while ($l_rack_length > 1) {
                    if ($l_by_ajax) {
                        $l_rack[] = [
                            'rack_index' => $l_index_start,
                            'rack_pos'   => $l_start_from + 1,
                            'value'      => $l_string,
                            'insertion'  => $l_insertion
                        ];
                    } else {
                        $l_rack[$l_start_from + 1] = $l_insertion;
                    }

                    $l_rack_length--;
                    $l_index_start++;
                    $l_start_from--;
                }

                if ($l_by_ajax) {
                    $l_rack[] = [
                        'rack_index' => $l_row['isys_catg_location_list__pos'],
                        'rack_pos'   => $l_rack_units + 1,
                        'value'      => $l_string,
                        'insertion'  => $l_insertion
                    ];
                } else {
                    $l_rack[$l_rack_units + 1] = $l_insertion;
                }

                $l_row = $l_res->get_row();
            }

            $l_rack_units--;
        }

        if ($l_by_ajax) {
            return [
                'units'          => $l_max_rack_unit,
                'assigned_units' => $l_rack
            ];
        }

        $l_objDAO = new isys_cmdb_dao_category_g_formfactor($this->get_database_component());

        // Get all possible hu positions from rack.
        $l_nHU = $l_objDAO->get_rack_hu($l_rack_obj_id);

        for ($i = $l_nHU;$i >= 1;$i--) {
            if (array_key_exists($i, $l_rack)) {
                $l_string = '';
                switch ($l_rack[$i]) {
                    case C__INSERTION__FRONT:
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED') . ')';
                        break;
                    case C__INSERTION__REAR:
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__BACKSIDE_OCCUPIED') . ')';
                        break;
                    case C__INSERTION__BOTH:
                        $l_string = '(' . isys_application::instance()->container->get('language')
                                ->get('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED') . ')';
                        break;
                }

                $l_return[$l_nHU - $i + 1] = $i . ' ' . $l_string;
            } else {
                $l_return[$l_nHU - $i + 1] = $i;
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the insertion dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_insertion(isys_request $p_request)
    {
        return [
            C__INSERTION__FRONT => isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATG__LOCATION_FRONT"),
            C__INSERTION__REAR  => isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATG__LOCATION_BACK"),
            C__INSERTION__BOTH  => isys_application::instance()->container->get('language')
                ->get("LC__CMDB__CATG__LOCATION_BOTH")
        ];
    }

    /**
     * Method for finding a free slot for a given object in a given rack.
     *
     * @param   integer $p_rack_id
     * @param   integer $p_insertion
     * @param   integer $p_object_to_assign
     * @param   integer $p_option
     *
     * @return  array
     */
    public function get_free_rackslots($p_rack_id, $p_insertion, $p_object_to_assign, $p_option, $p_rackSort = null)
    {
        $l_return = $l_used_slots = [];

        $l_sort_asc = (isys_tenantsettings::get('cmdb.rack.slot-assignment-sort-direction', 'asc') == 'asc');

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_rack_id)
                ->get_row();
        }

        // @See ID-4676
        if ($p_rackSort !== null) {
            $l_rack['isys_cats_enclosure_list__slot_sorting'] = $p_rackSort;
        }

        $l_obj = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_object_to_assign)
            ->get_row();

        $l_positions = $this->get_positions_in_rack($p_rack_id);

        if ($p_option == C__RACK_INSERTION__HORIZONTAL) {
            $l_obj_height = $l_obj['isys_catg_formfactor_list__rackunits'] ?: 1;

            $l_units = $l_positions['units'];
            $l_assigned_objects = $l_positions['assigned_units'];
            $l_chassis = [];

            if (is_array($l_assigned_objects)) {
                // Here we write the used slots in an array, so we can check it later with "in_array()".
                foreach ($l_assigned_objects as $l_slot) {
                    if ($l_slot['insertion'] === null || $l_slot['obj_id'] == $p_object_to_assign || $l_slot['option'] == C__RACK_INSERTION__VERTICAL ||
                        $l_slot['option'] == null) {
                        continue;
                    }

                    if ($l_slot['is_chassis']) {
                        for ($i = 0;$i < $l_slot['height'];$i++) {
                            $l_chassis[($l_slot['pos'] + $i) . '-' . $l_slot['insertion']] = [
                                'id'     => $l_slot['obj_id'],
                                'title'  => $l_slot['title'],
                                'height' => $l_slot['height']
                            ];
                        }
                    }

                    if ($l_slot['height'] > 1) {
                        for ($i = 0;$i < $l_slot['height'];$i++) {
                            $l_used_slots[] = ($l_slot['pos'] + $i) . '-' . $l_slot['insertion'];
                        }
                    } else {
                        $l_used_slots[] = $l_slot['pos'] . '-' . $l_slot['insertion'];
                    }
                }
            }

            for ($i = 1;$i <= $l_units;$i++) {
                $l_used_slot = false;

                if ($l_rack['isys_cats_enclosure_list__slot_sorting'] == 'desc') {
                    $l_num = ($l_units - $i) + 1;
                    $l_num_to = $l_num - $l_obj_height + 1;

                    if ($l_num_to < 1) {
                        continue;
                    }
                } else {
                    $l_num = $i;
                    $l_num_to = $i + $l_obj_height - 1;

                    if ($l_num_to > $l_units) {
                        continue;
                    }
                }

                if (isset($l_chassis[$i . '-' . $p_insertion])) {
                    $l_chassis_height = $l_chassis[$i . '-' . $p_insertion]['height'];

                    if ($l_sort_asc) {
                        $l_return[$i . ';' . $l_num . ';' . (($l_num + $l_chassis_height) - 1)] = isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $l_num . ($l_chassis_height > 1 ? ' &rarr; ' . (($l_num + $l_chassis_height) - 1) : '') .
                            ' (' . $l_chassis[$i . '-' . $p_insertion]['title'] . ')';
                    } else {
                        $l_return[$i . ';' . $l_num . ';' . (($l_num + $l_chassis_height) - 1)] = isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . ($l_chassis_height > 1 ? (($l_num + $l_chassis_height) - 1) . ' &rarr; ' : '') . $l_num .
                            ' (' . $l_chassis[$i . '-' . $p_insertion]['title'] . ')';
                    }

                    $i += ($l_chassis_height - 1);

                    continue;
                }

                // If the current row is in use, we don't need process the next lines.
                if (!self::slot_available($l_used_slots, $i, $p_insertion)) {
                    continue;
                }

                $l_tmp_to = $i + $l_obj_height - 1;

                for ($l_tmp = $i;$l_tmp <= $l_tmp_to;$l_tmp++) {
                    if (!self::slot_available($l_used_slots, $l_tmp, $p_insertion)) {
                        $l_used_slot = true;
                    }
                }

                if ($l_used_slot === false) {
                    if ($l_obj_height == 1) {
                        $l_return[$i . ';' . $l_num . ';' . $l_num] = isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $l_num;
                    } else {
                        if ($l_sort_asc) {
                            $l_return[$i . ';' . $l_num . ';' . $l_num_to] = isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $l_num . ' &rarr; ' . $l_num_to;
                        } else {
                            $l_return[$i . ';' . $l_num . ';' . $l_num_to] = isys_application::instance()->container->get('language')
                                    ->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $l_num_to . ' &rarr; ' . $l_num;
                        }
                    }
                }
            }
        } else {
            if (isset($l_positions['assigned_units']) && is_array($l_positions['assigned_units'])) {
                foreach ($l_positions['assigned_units'] as $l_slot) {
                    if ($l_slot['insertion'] === null || $l_slot['obj_id'] == $p_object_to_assign || $l_slot['option'] == C__RACK_INSERTION__HORIZONTAL ||
                        $l_slot['option'] == null) {
                        continue;
                    }

                    $l_used_slots[] = $l_slot['pos'] . '-' . $l_slot['insertion'];
                }
            }

            $l_insertion_pos = ($p_insertion == C__INSERTION__REAR) ? '_rear' : '_front';

            for ($i = 1;$i <= $l_rack['isys_cats_enclosure_list__vertical_slots' . $l_insertion_pos];$i++) {
                $l_num = $i;

                if (self::slot_available($l_used_slots, $l_num, $p_insertion)) {
                    $l_return[$l_num . ';' . $l_num] = 'Slot #' . $l_num;
                }
            }
        }

        return $l_return;
    }

    /**
     * Returns the options for the "position in rack" dialog.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_positions_in_rack($p_obj_id)
    {
        $l_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_obj_id)
            ->get_row();

        $l_return = [
            'units'          => $l_formfactor['isys_catg_formfactor_list__rackunits'],
            'assigned_units' => []
        ];

        $l_res = isys_cmdb_dao_location::instance($this->get_database_component())
            ->get_location($p_obj_id, null);

        while ($l_row = $l_res->get_row()) {
            $l_return['assigned_units'][] = [
                'obj_id'     => $l_row['isys_obj__id'],
                'title'      => $l_row['isys_obj__title'],
                'height'     => $l_row['isys_catg_formfactor_list__rackunits'] ?: 1,
                'option'     => $l_row['isys_catg_location_list__option'],
                'pos'        => $l_row['isys_catg_location_list__pos'],
                'insertion'  => $l_row['isys_catg_location_list__insertion'],
                'is_chassis' => $this->objtype_is_cats_assigned($l_row['isys_obj_type__id'], defined_or_default('C__CATS__CHASSIS'))
            ];
        }

        return $l_return;
    }

    /**
     * Method for retrieving the location parent of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  isys_exception_database
     */
    public function get_parent_id_by_object($p_obj_id)
    {
        if ($p_obj_id > 0) {
            if (isset(self::$m_location_cache[$p_obj_id])) {
                return self::$m_location_cache[$p_obj_id]['parent'];
            }

            $l_sql = 'SELECT isys_catg_location_list__parentid AS parent,
				isys_obj__title AS title
				FROM isys_obj
				LEFT JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_obj__id
				WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            $l_parent = $this->retrieve($l_sql)
                ->get_row();

            if (is_array($l_parent)) {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => $l_parent['title'],
                    'parent' => $l_parent['parent'] ?: false
                ];
            } else {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => null,
                    'parent' => false
                ];
            }

            return self::$m_location_cache[$p_obj_id]['parent'];
        }

        return false;
    }

    /**
     * @param null $p_filter
     * @param int  $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_container_objects($p_filter = null, $p_status = C__RECORD_STATUS__NORMAL, $p_consider_rights = false)
    {
        $l_filter = '';

        if ($p_consider_rights) {
            $l_filter = isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition();
        }

        $l_filter .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);

        if ($p_filter !== null) {
            $l_filter .= ' AND isys_obj__title LIKE ' . $this->convert_sql_text('%' . $p_filter . '%');
        }

        return $this->get_data(null, null, 'AND isys_obj_type__container = 1' . $l_filter, null, $p_status);
    }

    /**
     * @param   integer $p_rack_obj_id
     * @param   boolean $p_front
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_rack_positions($p_rack_obj_id, $p_front = true)
    {
        $l_sql = 'SELECT * FROM isys_catg_location_list
			INNER JOIN isys_catg_global_list ON isys_catg_location_list__isys_obj__id = isys_catg_global_list__isys_obj__id
			WHERE isys_catg_location_list__pos > 0
			AND isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_rack_obj_id);

        if ($p_front) {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 1);';
        } else {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 0);';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Save global category location element
     *
     * @param   integer $p_cat_level        Level to save, default 0.
     * @param   integer &$p_intOldRecStatus __status of record before update.
     * @param   boolean $p_create           Decides whether to create or to save.
     *
     * @return  null
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_location_list__status"];
        $l_oldParent = $l_catdata["isys_catg_location_list__parentid"];

        if (!empty($l_catdata["isys_catg_location_list__id"])) {
            $coord = null;
            if ($_POST["C__CATG__LOCATION_LATITUDE"] || $_POST["C__CATG__LOCATION_LONGITUDE"]) {
                $coord = new Coordinate([
                    str_replace(',', '.', $_POST["C__CATG__LOCATION_LATITUDE"]) ?: 0,
                    str_replace(',', '.', $_POST["C__CATG__LOCATION_LONGITUDE"]) ?: 0
                ]);
            }

            $l_return = $this->save(
                $l_catdata["isys_catg_location_list__id"],
                $l_catdata["isys_catg_location_list__isys_obj__id"],
                $_POST['C__CATG__LOCATION_PARENT__HIDDEN'],
                $l_oldParent,
                $_POST["C__CATG__LOCATION_POS"],
                $_POST["C__CATG__LOCATION_INSERTION"],
                $_POST["C__CATG__LOCATION_IMAGE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__LOCATION_OPTION'],
                $coord,
                $_POST['C__CATG__LOCATION_SLOT__selected_values'],
                $_POST["C__CATG__LOCATION_SNMP_SYSLOCATION"]
            );

            return $l_return;
        }

        return null;
    }

    /**
     * Method for saving a object to
     *
     * @param integer $p_object_id
     * @param string  $p_slot
     *
     * @return bool
     */
    public function save_object_to_segment($p_object_id, $p_slot)
    {

        // Instead: assign this object to the segment object and assign the slot.
        $l_chassis_dao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
        $l_chassis_slot_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db);

        $l_chassis_object = $l_chassis_slot_dao->get_data($p_slot)
            ->get_row();

        // First we assign the object to the chassis
        isys_cmdb_dao_location::instance($this->m_db)
            ->attach($p_object_id, $l_chassis_object['isys_obj__id']);

        $l_slots = [];
        $l_slot_raw = explode(',', $p_slot);

        foreach ($l_slot_raw as $l_slot) {
            $l_slots[] = ['id' => $l_slot];
        }

        // Delete all old connections between chassis and $p_object_id.
        $l_slots_result = $l_chassis_dao->get_slots_by_assiged_object($p_object_id);

        while ($l_slots_row = $l_slots_result->get_row()) {
            $l_chassis_dao->rank_record($l_slots_row['isys_cats_chassis_list__id'], C__CMDB__RANK__DIRECTION_DELETE, 'isys_cats_chassis_list', null, true);
        }

        $l_chassis_dao->sync([
            'properties' => [
                'assigned_device' => [C__DATA__VALUE => $p_object_id],
                'assigned_slots'  => [C__DATA__VALUE => $l_slots]
            ]
        ], $l_chassis_object['isys_obj__id'], isys_import_handler_cmdb::C__CREATE);

        // Let the parent and relation be re-set by the sync method.
        $this->sync([
            'properties' => [
                'parent' => [C__DATA__VALUE => $l_chassis_object['isys_obj__id']]
            ]
        ], $p_object_id, isys_import_handler_cmdb::C__UPDATE);

        $this->setCacheFilesToRemove();

        return true;
    }

    /**
     * Creates the category entry.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_parent_object_id
     * @param   integer    $p_posID
     * @param   integer    $p_insertion
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_status
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     * @param   string     $p_slot
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function save_category(
        $p_list_id,
        $p_parent_object_id,
        $p_posID,
        $p_insertion = null,
        $p_unused = null,
        $p_description = '',
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_option = null,
        $p_coord = null,
        $p_slot = null,
        $snmpSysLocation = null
    ) {
        $l_data = $this->get_data($p_list_id)
            ->get_row();

        // In case of "create" context, the "$l_data" variable might be empty.
        $l_parent = ($p_parent_object_id ?: $l_data['isys_catg_location_list__parentid']);

        $l_parent_row = $this->get_data(null, $l_parent)
            ->get_row();

        if (empty($p_parent_object_id) && $l_parent_row['isys_obj_type__const'] === isys_tenantsettings::get('cmdb.rack.segment-template-object-type', 'C__OBJTYPE__RACK_SEGMENT')) {
            $chassisDao = new isys_cmdb_dao_category_s_chassis($this->m_db);
            $objects = $chassisDao->get_assigned_objects($l_parent_row['isys_catg_location_list__isys_obj__id']);
            foreach ($objects as $object) {
                if ($object['isys_obj__id'] === $l_data['isys_obj__id']) {
                    $chassisDao->remove_slot_assignments($object['isys_cats_chassis_list__id']);
                    $chassisDao->relations_remove($object['isys_cats_chassis_list__id'], $object['isys_cats_chassis_list__isys_obj__id'], $object['isys_obj__id']);
                    $chassisDao->delete_entry($object['isys_cats_chassis_list__id'], 'isys_cats_chassis_list');
                }
            }
            $p_slot = null;
        }
        if (!empty($p_slot)) {
            return $this->save_object_to_segment($l_data['isys_obj__id'], $p_slot);
        }

        // Reset Insertion, position and option if parent object has not the rack category or is empty or if the object type of the current object can not be positioned in a rack
        if (($p_parent_object_id > 0 && $l_parent_row['isys_obj_type__isysgui_cats__id'] != defined_or_default('C__CATS__ENCLOSURE')) || empty($p_parent_object_id) ||
            !$l_data['isys_obj_type__show_in_rack']) {
            $p_insertion = null;
            $p_posID = null;
            $p_option = null;
        } elseif ($p_parent_object_id > 0 && $l_parent_row['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__ENCLOSURE') && class_exists('isys_cmdb_dao_category_s_enclosure')) {
            // check if data in the specific category exists
            $rackDao = isys_cmdb_dao_category_s_enclosure::instance(isys_application::instance()->database);
            $parentRackData = $rackDao->get_data(null, $p_parent_object_id)
                ->get_row();
            if (!$parentRackData) {
                // Create default entry in specific category
                $rackDao->create_data([
                    'isys_obj__id'         => $p_parent_object_id,
                    'vertical_slots_front' => 0,
                    'vertical_slots_rear'  => 0,
                    'slot_sorting'         => 'asc',
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
            }
        }

        if (!$p_coord) {
            $p_coord = null;
        }

        $l_strSQL = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parent_object_id) . ",
			isys_catg_location_list__pos = " . $this->convert_sql_id($p_posID) . ",
			isys_catg_location_list__insertion = " . $this->convert_sql_int($p_insertion) . ",
			isys_catg_location_list__gps = " . $this->convert_sql_point($p_coord) . ",
			isys_catg_location_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_location_list__status = " . $this->convert_sql_id($p_status) . ",
			isys_catg_location_list__option = " . $this->convert_sql_id($p_option) . ",
			isys_catg_location_list__snmp_syslocation = " . $this->convert_sql_text($snmpSysLocation) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_list_id) . ";";

        $this->m_strLogbookSQL = $l_strSQL;

        $l_bRet = $this->update($l_strSQL) && $this->apply_update();

        if ($l_bRet) {
            // Create implicit relation.
            try {
                if ($p_parent_object_id > 0) {
                    isys_cmdb_dao_category_g_relation::instance($this->m_db)
                        ->handle_relation(
                            $p_list_id,
                            "isys_catg_location_list",
                            defined_or_default('C__RELATION_TYPE__LOCATION'),
                            $l_data["isys_catg_location_list__isys_catg_relation_list__id"],
                            $p_parent_object_id,
                            $l_data["isys_catg_location_list__isys_obj__id"]
                        );
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        // log rack server assignment
        $message = $l_parent_row['isys_obj__title'] .
            ' (Type "' .
            isys_application::instance()->container->get('language')->get($l_parent_row['isys_obj_type__title']) .
            '"): ' .
            isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__LOCATION__OBJECT_CHANGED');

        isys_component_dao_logbook::instance(isys_application::instance()->database)
            ->set_entry(
                $message,
                '',
                null,
                defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                $l_parent_row['isys_obj__id'],
                $l_parent_row['isys_obj__title'],
                $l_parent_row['isys_obj_type__title'],
                $l_parent_row['isys_obj_type__title']
            );

        $this->setCacheFilesToRemove();

        return $l_bRet;
    }

    /**
     * Creates the category entry.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_object_id
     * @param   integer    $p_parent_object_id
     * @param   integer    $p_posID
     * @param   integer    $p_frontsideID
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_status
     * @param   Coordinate $p_coord
     * @param   integer    $p_option
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function create_category(
        $p_list_id = null,
        $p_object_id,
        $p_parent_object_id,
        $p_posID,
        $p_frontsideID,
        $p_unused = null,
        $p_description = '',
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_coord = null,
        $p_option = null,
        $snmpSysLocation = null
    ) {
        if ($p_frontsideID >= 0 && $p_frontsideID !== null) {
            $p_frontside = "'" . $p_frontsideID . "'";
        } else {
            $p_frontside = "NULL";
        }

        if (is_null($p_coord)) {
            $p_coord = new Coordinate([0, 0]);
        }

        $l_sql = 'INSERT IGNORE INTO isys_catg_location_list SET
			isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_parent_object_id) . ',
			isys_catg_location_list__gps = ' . $this->convert_sql_point($p_coord) . ',
			isys_catg_location_list__pos = ' . $this->convert_sql_id($p_posID) . ',
			isys_catg_location_list__option = ' . $this->convert_sql_int($p_option) . ',
			isys_catg_location_list__insertion = ' . $p_frontside . ',
			isys_catg_location_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_location_list__snmp_syslocation = ' . $this->convert_sql_text($snmpSysLocation) . ',
			isys_catg_location_list__status = ' . $this->convert_sql_int($p_status) . ';';

        $this->update($l_sql) && $this->apply_update();
        $this->m_strLogbookSQL .= $l_sql;
        $l_last_id = $this->get_last_insert_id();

        // Create implicit relation.
        try {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (!empty($p_parent_object_id)) {
                $l_dao_relation->handle_relation($l_last_id, 'isys_catg_location_list', defined_or_default('C__RELATION_TYPE__LOCATION'), null, $p_parent_object_id, $p_object_id);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->setCacheFilesToRemove();

        return $l_last_id;
    }

    /**
     * More simple method for resetting a location.
     *
     * @param   integer $p_obj_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function reset_location($p_obj_id)
    {
        if ($p_obj_id > 0) {
            $l_sql = 'UPDATE isys_catg_location_list
                SET isys_catg_location_list__parentid = NULL
                WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            $this->update($l_sql);
            $this->setCacheFilesToRemove();
        }
    }

    /**
     * Executes the operations to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_objID
     * @param   integer    $p_parent_id
     * @param   integer    $p_oldParentID
     * @param   integer    $p_posID
     * @param   integer    $p_insertion
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     * @param   slot       $p_slot
     *
     * @return  boolean
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save(
        $p_list_id,
        $p_objID,
        $p_parent_id,
        $p_oldParentID = null,
        $p_posID = null,
        $p_insertion = null,
        $p_unused = null,
        $p_description = '',
        $p_option = null,
        $p_coord = null,
        $p_slot = null,
        $snmpSysLocation = null
    ) {
        if ($p_list_id > 0) {
            if ($p_parent_id != 'NULL' && $p_parent_id > 0) {
                if ($this->obj_exists($p_parent_id)) {
                    if ($p_objID != $p_parent_id) {
                        if (empty($p_oldParentID)) {
                            $this->insert_node($p_list_id, $p_parent_id);
                        } else {
                            $this->move_node($p_list_id, $p_parent_id);
                        }
                    } else {
                        throw new isys_exception_dao_cmdb('Attaching own object is prohibitted.');
                    }
                } else {
                    throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parent_id));
                }
            } else {
                if ($p_oldParentID > 0) {
                    $this->delete_node($p_list_id);
                }
            }

            return $this->save_category(
                $p_list_id,
                $p_parent_id,
                $p_posID,
                $p_insertion,
                null,
                $p_description,
                C__RECORD_STATUS__NORMAL,
                $p_option,
                $p_coord,
                $p_slot,
                $snmpSysLocation
            );
        }

        return false;
    }

    /**
     * Executes the operations to create the category entry referenced by isys_obj__id $p_objID
     *
     * @param   integer    $p_objID
     * @param   integer    $p_parentID
     * @param   integer    $p_posID
     * @param   integer    $p_frontsideID
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     *
     * @return  integer  The newly created ID or false
     * @throws  Exception
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_parentID,
        $p_posID = null,
        $p_frontsideID = null,
        $p_unused = null,
        $p_description = '',
        $p_option = null,
        $p_coord = null,
        $snmpSysLocation = null
    ) {
        if ($p_parentID > 0) {
            if (!$this->obj_exists($p_parentID)) {
                throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parentID));
            }
        }

        $l_insID = $this->create_category(
            null,
            $p_objID,
            $p_parentID,
            $p_posID,
            $p_frontsideID,
            null,
            $p_description,
            C__RECORD_STATUS__NORMAL,
            $p_coord,
            $p_option,
            $snmpSysLocation
        );

        if ($p_parentID != null) {
            $this->insert_node($l_insID, $p_parentID);
        }

        return $l_insID;
    }

    /**
     * Get whole location tree with one query.
     *
     * @return  isys_component_dao_result
     */
    public function get_location_tree()
    {
        $l_sql = 'SELECT isys_obj__id AS id, a.isys_catg_location_list__parentid AS parentid, isys_obj__title AS title, isys_obj_type__id AS object_type_id, isys_obj_type__title AS object_type, a.isys_catg_location_list__isys_catg_relation_list__id AS relation_id
			FROM isys_catg_location_list a
			INNER JOIN isys_catg_location_list b ON a.isys_catg_location_list__parentid = b.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj ON isys_obj__id = a.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			ORDER BY a.isys_catg_location_list__parentid ASC';

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_cached_locations($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            return self::$m_location_cache[$p_obj_id];
        }

        return self::$m_location_cache;
    }

    /**
     * Returns the location path of the given object. Will throw an RuntimeException on recursion!
     *
     * @param   integer $p_obj
     *
     * @return  array
     * @throws  RuntimeException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_location_path($p_obj)
    {
        $l_return = [];
        $l_parentid = $p_obj;

        while (($l_parentid = $this->get_parent_id_by_object($l_parentid)) !== false) {
            if (in_array($l_parentid, $l_return)) {
                throw new RuntimeException(isys_application::instance()->container->get('language')
                        ->get('LC__CATG__LOCATION__RECURSION_IN_OBJECT') . ' #' . $l_parentid . ' "' . $this->get_obj_name_by_id_as_string($l_parentid) . '"');
            }

            if (defined('C__OBJ__ROOT_LOCATION') && $l_parentid != C__OBJ__ROOT_LOCATION) {
                $l_return[] = $l_parentid;
            }
        }

        return $l_return;
    }

    /**
     * Default Event triggered on updating the location nodes.
     *
     * @param   int    $p_nodeID
     * @param   int    $p_parentNodeID
     * @param   string $p_updatetype
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function update_location_node($p_nodeID, $p_parentNodeID, $p_updatetype)
    {
        // Invalidate cache. This is needed for cached location rights:
        isys_cache::keyvalue()
            ->flush();
    }

    /**
     * Creates a new node in the lft-rgt-tree.
     *
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @throws  Exception
     */
    public function insert_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'insert');

        $l_query = "SELECT isys_catg_location_list__rgt
	        FROM isys_catg_location_list
	        WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";

        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_rgt = (empty($l_row["isys_catg_location_list__rgt"])) ? 0 : ((int)$l_row["isys_catg_location_list__rgt"]) - 1;

        $l_update = "UPDATE isys_catg_location_list SET
	        isys_catg_location_list__rgt = isys_catg_location_list__rgt + 2
	        WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + 2
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = " . $this->convert_sql_id($l_rgt + 1) . ",
			isys_catg_location_list__rgt = " . $this->convert_sql_id($l_rgt + 2) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }
        $this->setCacheFilesToRemove();
    }

    /**
     * Delete a node from the tree.
     *
     * @param   integer $p_nodeID
     *
     * @throws  Exception
     */
    public function delete_node($p_nodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, null, 'delete');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_lft = (int)$l_row["isys_catg_location_list__lft"];
        $l_rgt = (int)$l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        if ($l_lft > 0 && $l_rgt > 0 && $l_rgt > $l_lft) {
            // Delete relations
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_sql = "SELECT isys_catg_location_list__isys_catg_relation_list__id
				FROM isys_catg_location_list
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_dao_relation->delete_relation($l_row["isys_catg_location_list__isys_catg_relation_list__id"]);
                }
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = NULL,
				isys_catg_location_list__rgt = NULL
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
				WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
				WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $this->setCacheFilesToRemove();
        }
    }

    /**
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @return  boolean
     * @throws  Exception
     */
    public function move_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'move');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt, isys_catg_location_list__parentid
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_lft = (int)$l_row["isys_catg_location_list__lft"];
        $l_rgt = (int)$l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        $l_subElements = [];
        $l_query = "SELECT isys_catg_location_list__id
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row()) {
            if ($l_row['isys_catg_location_list__id'] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                continue;
            }

            $l_subElements[] = $l_row['isys_catg_location_list__id'];
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_query = "SELECT isys_catg_location_list__rgt, isys_catg_location_list__lft
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_rgtNew = ((int)$l_row["isys_catg_location_list__rgt"]) - 1;
        $l_lftNew = ((int)$l_row["isys_catg_location_list__lft"]) + 1;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgtNew;

        if (count($l_subElements) > 0) {
            $l_in_query = implode(',', $l_subElements);
            $l_in_query = rtrim($l_in_query, ',');
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgtNew;

        if (count($l_subElements) > 0) {
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        // TODO: Insert subtree at the new position
        $l_rgtNew += $l_diff;
        $l_diff = $l_rgtNew - $l_rgt;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + (" . $l_diff . "),
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + (" . $l_diff . ")
			WHERE FALSE";

        if (count($l_subElements) > 0) {
            $l_update .= ' OR isys_catg_location_list__id IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }
        $this->setCacheFilesToRemove();

        return $this->apply_update();
    }

    /**
     * Checks whether location is well-defined. This helps to avoid self-
     * referencing, non-location objects, and referencing loops.
     *
     * @param int $p_obj_id      Object identifier
     * @param int $p_location_id Location identifier
     *
     * @return bool|string Returns true on success, otherwise error message.
     */
    public function validate_parent($p_obj_id, $p_location_id)
    {
        assert(is_int($p_obj_id) && $p_obj_id > 0);
        assert(is_int($p_location_id) && $p_location_id > 0);

        // Avoid self-referencing:
        if ($p_obj_id === $p_location_id) {
            return isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        }

        $l_sql = 'SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__container = 1';
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_valid_location_object_types[] = $l_row['isys_obj_type__id'];
        }

        // Location must be a location object:
        $l_obj_type = intval($this->get_objTypeID($p_location_id));
        if (!in_array($l_obj_type, $l_valid_location_object_types)) {
            return isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        }

        $l_parent_id = $p_location_id;
        $l_location_objects = [];

        // Walk through the location tree:
        while ($l_parent_id !== false) {

            // Object itself isn't part of the tree:
            if (in_array($p_obj_id, $l_location_objects)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Root location has no parent.
            if ($l_parent_id == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                break;
            }

            // Location isn't part of the tree:
            if (in_array($l_parent_id, $l_location_objects)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Parent must be a location object:
            $l_obj_type = intval($this->get_objTypeID($l_parent_id));
            if (!in_array($l_obj_type, $l_valid_location_object_types)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Keep parent in mind:
            $l_location_objects[] = $l_parent_id;

            // Next one...
            $l_parent_id = intval($this->get_parent_id_by_object($l_parent_id));
        }

        return true;
    }

    /**
     * This method gets called before "category save" or "category create" by signal-slot-system.
     *
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     ID-4974  When saving the category, we check if the location changes need to be handled.
     */
    public function validate_before_save()
    {
        if (isys_tenantsettings::get('cmdb.chassis.handle-location-changes', false)) {
            $db = isys_application::instance()->container->get('database');
            $objectId = (int)$_GET[C__CMDB__GET__OBJECT];
            $parent = isset($_POST['C__CATG__LOCATION_PARENT__HIDDEN']) ? (int)$_POST['C__CATG__LOCATION_PARENT__HIDDEN'] : null;

            if ($parent === null) {
                $parent = $this->get_parent_id_by_object($objectId);
            }

            $chassisDao = isys_cmdb_dao_category_s_chassis::instance($db);
            $result = $chassisDao->get_slots_by_assiged_object($objectId);

            while ($row = $result->get_row()) {
                if ($row['isys_cats_chassis_slot_list__isys_obj__id'] != $parent) {
                    $chassisDao->relations_remove($row['isys_cats_chassis_list__id'], null, $objectId);
                }
            }
        }
    }

    /**
     * This method gets called after "category save" or "category create" by signal-slot-system.
     *
     * @throws  RuntimeException
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function validate_after_save()
    {
        $db = isys_application::instance()->container->get('database');
        $objectId = (int)$_GET[C__CMDB__GET__OBJECT];

        try {
            $objTypeID = $this->get_objTypeID($objectId);

            if ($this->get_location_path($objectId) && $this->objtype_is_catg_assigned($objTypeID, filter_defined_constants(['C__CATG__LOGICAL_UNIT', 'C__CATG__LOCATION'])) &&
                isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
                $daoLogicalUnit = isys_cmdb_dao_category_g_logical_unit::instance($db);

                // Remove logical unit if location has been set. Otherwise there will be a conflict in the combined location view. See ID-4425
                $result = $daoLogicalUnit->retrieve('SELECT isys_catg_logical_unit_list__id, isys_catg_logical_unit_list__isys_obj__id__parent 
                    FROM isys_catg_logical_unit_list 
                    WHERE isys_catg_logical_unit_list__isys_obj__id = ' . $daoLogicalUnit->convert_sql_id($objectId));

                if ($result->num_rows() == 1) {
                    $logicalUnitData = $result->get_row();
                    $logicalUnitId = $logicalUnitData['isys_catg_logical_unit_list__id'];
                    $logicalUnitParent = $logicalUnitData['isys_catg_logical_unit_list__isys_obj__id__parent'];

                    $daoLogicalUnit->save($logicalUnitId, null);

                    $l_changes = [
                        'isys_cmdb_dao_category_g_logical_unit::parent' => [
                            'from' => $daoLogicalUnit->obj_get_title_by_id_as_string($logicalUnitParent),
                            'to'   => ''
                        ]
                    ];

                    $this->setCacheFilesToRemove();
                    $l_event_manager = isys_event_manager::getInstance();
                    $l_changes_compressed = serialize($l_changes);
                    $l_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', '', $objectId, $objTypeID, 'LC__CMDB__CATG__LOGICAL_UNIT', $l_changes_compressed);
                }
            }
        } catch (RuntimeException $e) {
            // The saved parent location produces a recursion - We set the parent to NULL.
            $this->reset_location($objectId);

            // And now we throw the exception further towards the action handler.
            throw $e;
        }
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_location_path' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_PATH',
                'isys_catg_location_list__parentid',
                'isys_catg_location_list',
                [
                    $this,
                    'dynamic_property_callback_location_path'
                ]
            ),
            '_location_path_raw' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_PATH_RAW',
                'isys_catg_location_list__parentid',
                'isys_catg_location_list',
                [
                    $this,
                    'dynamic_property_callback_location_path_raw'
                ]
            ),
            '_pos' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_POS',
                'isys_catg_location_list__id',
                'isys_catg_location_list',
                [
                    $this,
                    'retrievePositionInRack'
                ]
            )
        ];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *, ST_AsText(isys_catg_location_list__gps) AS isys_catg_location_list__gps, ST_X(isys_catg_location_list__gps) AS latitude, ST_Y(isys_catg_location_list__gps) AS longitude FROM isys_catg_location_list
			INNER JOIN isys_obj
			ON isys_catg_location_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type
			ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_location_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_location_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method which builds the location path as query
     *
     * @param int    $p_count
     * @param int    $maxLengthLocationPath
     * @param int    $maxLengthLocationObjects
     * @param string $p_where
     * @param bool   $withoutObjectIdPlaceholder
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function build_location_path_query($p_count = 1, $maxLengthLocationPath = 100, $maxLengthLocationObjects = 16, $p_where = '', $withoutObjectIdPlaceholder = false)
    {
        $l_separator = isys_tenantsettings::get('gui.separator.location', ' > ');

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $l_separator = '<span style="direction: ltr;width: 15px;display: inline-block;text-align: center;">' . isys_tenantsettings::get('gui.separator.location', ' > ') . '</span>';
        }

        $l_query = 'SELECT ';
        $l_joins = '';

        $p_count = max((int) $p_count, 1);
        $l_locationPath = ' CONCAT_WS(\'' . $l_separator . ' \', ';

        $htmlLengthAll = 0;
        $htmlLengthSingleElement = 0;

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $htmlLengthSingleElement = strlen('<style>td[data-property="isys_cmdb_dao_category_g_location__location_path"] span.overflowable {direction: rtl;}</style>');

            $l_locationPath .= ' CONCAT(\'<style>td[data-property="isys_cmdb_dao_category_g_location__location_path"] span.overflowable {direction: rtl;}</style>\', CONCAT_WS(\'' .
                $l_separator . ' \', ';
        }

        $l_locationPaths = [];

        // Locationpath
        for ($i = $p_count; $i > 0; $i--) {
            $l_locationPaths[$i] = '(SELECT CONCAT(isys_obj__title ' . (!$withoutObjectIdPlaceholder ? ", ' {', isys_obj__id, '}'" : "") . ') 
                FROM isys_obj 
                WHERE sub' . $i . '.isys_catg_location_list__isys_obj__id = isys_obj__id)';

            $htmlLengthAll += $htmlLengthSingleElement;
        }

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $l_locationPaths = array_reverse($l_locationPaths);
        }

        $l_locationPath = $l_locationPath . implode(',', $l_locationPaths) . (isys_tenantsettings::get('gui.location_path.direction.rtl', false) ? '))' : '') . ') AS title';

        // JOINS
        $l_previousAlias = 'main';
        for ($i = 1; $i <= $p_count; $i++) {
            $l_alias = 'sub' . $i;
            $l_joins .= ' LEFT JOIN isys_catg_location_list AS ' . $l_alias . ' ON ' . $l_alias . '.isys_catg_location_list__isys_obj__id = ' . $l_previousAlias . '.isys_catg_location_list__parentid ';
            $l_previousAlias = $l_alias;
        }

        $l_joins .= ' LEFT JOIN isys_obj as objMain ON objMain.isys_obj__id = main.isys_catg_location_list__isys_obj__id';

        return $l_query . $l_locationPath . ' FROM isys_catg_location_list AS main ' . $l_joins . $p_where;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        $language = isys_application::instance()->container->get('language');
        return [
            'location_path'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location path'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__parentid',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(self::build_location_path_query(isys_tenantsettings::get(
                        'cmdb.limits.location-path',
                        5
                    ), isys_tenantsettings::get('maxlength.location.path', 100), isys_tenantsettings::get('maxlength.location.objects', 16)), 'isys_catg_location_list', 'main.isys_catg_location_list__id', 'main.isys_catg_location_list__isys_obj__id')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ]),
            'parent'           => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_location_list__parentid',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__LOCATION'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_location',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_location',
                        true
                    ]),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_catg_location_list
                              INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__parentid',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        null,
                        null,
                        'isys_catg_location_list__parentid'
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_location_list',
                            'LEFT',
                            'isys_catg_location_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__LOCATION_PARENT',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strPopupType'  => 'browser_location',
                        'callback_accept' => 'if($(\'C__CATG__LOCATION_PARENT__HIDDEN\')){$(\'C__CATG__LOCATION_PARENT__HIDDEN\').fire(\'locationObject:selected\');}',
                        'callback_detach' => 'if($(\'C__CATG__LOCATION_PARENT__HIDDEN\')){$(\'C__CATG__LOCATION_PARENT__HIDDEN\').fire(\'locationObject:selected\');}',
                        'containers_only' => true
                    ],
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'location'
                    ]
                ]
            ]),
            'option'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_OPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assembly option'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__option',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_location_list__option = ' .
                        $this->convert_sql_id(C__RACK_INSERTION__HORIZONTAL) . ' THEN \'LC__CMDB__CATS__ENCLOSURE__HORIZONTAL\'
                            WHEN isys_catg_location_list__option = ' . $this->convert_sql_id(C__RACK_INSERTION__VERTICAL) . ' THEN \'LC__CMDB__CATS__ENCLOSURE__VERTICAL\'
                            ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END)
                            FROM isys_catg_location_list',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_OPTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_assembly_options'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'insertion'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_FRONTSIDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Insertion'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__insertion',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_location_list__insertion = ' .
                        $this->convert_sql_int(C__INSERTION__REAR) . ' THEN \'LC__CATG__LOCATION__BACKSIDE_OCCUPIED\'
                            WHEN isys_catg_location_list__insertion = ' . $this->convert_sql_int(C__INSERTION__FRONT) . ' THEN \'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED\'
                            WHEN isys_catg_location_list__insertion = ' . $this->convert_sql_int(C__INSERTION__BOTH) . ' THEN \'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED\'
                            ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END) AS title
                            FROM isys_catg_location_list',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id'),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_INSERTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'        => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_insertion'
                        ]),
                        'p_strSelectedID' => C__INSERTION__FRONT
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'pos'              => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_POS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Position in the rack'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__pos',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_enclosure_list__slot_sorting = ' .
                        $this->convert_sql_text('asc') . ' AND isys_catg_location_list__pos > 0
                                    THEN CONCAT_WS(\' \', ' . $this->convert_sql_text($language->get('LC__CMDB__CATG__RACKUNITS_ABBR')) . ',
                                    isys_catg_location_list__pos, ' . $this->convert_sql_text('->') . ',
                                    isys_catg_location_list__pos + f2.isys_catg_formfactor_list__rackunits - 1)
                                  WHEN isys_cats_enclosure_list__slot_sorting = ' . $this->convert_sql_text('desc') .
                        ' AND isys_catg_location_list__pos > 0 THEN CONCAT_WS(\' \', ' . $this->convert_sql_text('HE ') . ',
                                  (f1.isys_catg_formfactor_list__rackunits + 1 - isys_catg_location_list__pos), ' . $this->convert_sql_text('->') . ',
                                  (f1.isys_catg_formfactor_list__rackunits + 1 - isys_catg_location_list__pos - f2.isys_catg_formfactor_list__rackunits + 1))
                                ELSE \'\' END)
                            FROM isys_catg_location_list
                            INNER JOIN isys_catg_formfactor_list f1 ON f1.isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__parentid
                            INNER JOIN isys_cats_enclosure_list ON isys_cats_enclosure_list__isys_obj__id = isys_catg_location_list__parentid
                            INNER JOIN isys_catg_formfactor_list f2 ON f2.isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__isys_obj__id',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_formfactor_list',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_catg_formfactor_list__id',
                            '',
                            'f1',
                            'f1',
                            'isys_catg_location_list'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_enclosure_list',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_cats_enclosure_list__id',
                            '',
                            '',
                            '',
                            'isys_catg_location_list'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_formfactor_list',
                            'LEFT',
                            'isys_catg_location_list__isys_obj__id',
                            'isys_catg_formfactor_list__isys_obj__id',
                            '',
                            'f2',
                            'f2',
                            'isys_catg_location_list'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_POS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_pos'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'location_property_pos'
                    ]
                ]
            ]),
            'gps'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'GPS',
                    C__PROPERTY__INFO__DESCRIPTION => 'GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__gps'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_gps'
                    ]
                ]
            ]),
            'latitude'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LATITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Latitude of GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__gps',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT X(isys_catg_location_list__gps)
                            FROM isys_catg_location_list', 'isys_catg_location_list', 'isys_catg_location_list__id', 'isys_catg_location_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__FIELD_FUNCTION => function ($field) {
                        return 'X(' . $field . ')';
                    },
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_latitude'
                    ]
                ]
            ]),
            'longitude'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LONGITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Longitude of GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__gps',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT Y(isys_catg_location_list__gps)
                            FROM isys_catg_location_list', 'isys_catg_location_list', 'isys_catg_location_list__id', 'isys_catg_location_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__FIELD_FUNCTION => function ($field) {
                        return 'Y(' . $field . ')';
                    },
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_longitude'
                    ]
                ]
            ]),
            'snmp_syslocation' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION__SNMP_SYSLOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'SNMP Syslocation'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__snmp_syslocation',
                    C__PROPERTY__DATA__INDEX => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_SNMP_SYSLOCATION'
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LOCATION', 'C__CATG__LOCATION')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
                ]
            ])
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_coord = null;

            if (isset($p_category_data['properties']['gps'][C__DATA__VALUE]) && is_array($p_category_data['properties']['gps'][C__DATA__VALUE]) &&
                ($p_category_data['properties']['gps'][C__DATA__VALUE][0] || $p_category_data['properties']['gps'][C__DATA__VALUE][1])) {
                $l_gps = $p_category_data['properties']['gps'][C__DATA__VALUE];

                if (is_numeric($l_gps[0]) && is_numeric($l_gps[1])) {
                    $l_coord = new Coordinate($l_gps);
                }
            } else {
                if ($p_category_data['properties']['latitude'][C__DATA__VALUE] || $p_category_data['properties']['longitude'][C__DATA__VALUE]) {
                    $l_coord = new Coordinate([
                        str_replace(',', '.', $p_category_data['properties']['latitude'][C__DATA__VALUE]) ?: 0,
                        str_replace(',', '.', $p_category_data['properties']['longitude'][C__DATA__VALUE]) ?: 0
                    ]);
                }
            }

            // @see ID-4543: If position is empty then property insertion and option has to be empty otherwise it won´t be available in the rack view as
            //               unassigned object
            if (empty($p_category_data['properties']['pos'][C__DATA__VALUE])) {
                $p_category_data['properties']['insertion'][C__DATA__VALUE] = null;
                $p_category_data['properties']['option'][C__DATA__VALUE] = null;
                $p_category_data['properties']['pos'][C__DATA__VALUE] = null;
            }

            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create(
                    $p_object_id,
                    $p_category_data['properties']['parent'][C__DATA__VALUE],
                    $p_category_data['properties']['pos'][C__DATA__VALUE] ?: null,
                    $p_category_data['properties']['insertion'][C__DATA__VALUE] ?: null,
                    null,
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['option'][C__DATA__VALUE] ?: null,
                    $l_coord,
                    $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE] ?: null
                );
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                if ($p_category_data['data_id'] === null) {
                    $l_res = $this->retrieve('SELECT isys_catg_location_list__id FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                        $this->convert_sql_id($p_object_id) . ';');

                    if (is_countable($l_res) && count($l_res)) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_location_list__id');
                    } else {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['parent'][C__DATA__VALUE],
                            $p_category_data['properties']['pos'][C__DATA__VALUE] ?: null,
                            $p_category_data['properties']['insertion'][C__DATA__VALUE] ?: null,
                            null,
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['option'][C__DATA__VALUE] ?: null,
                            $l_coord,
                            $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE] ?: null
                        );
                    }
                }

                // Save category data:
                if ($p_category_data['data_id'] > 0) {
                    $this->save(
                        $p_category_data['data_id'],
                        $p_object_id,
                        $p_category_data['properties']['parent'][C__DATA__VALUE],
                        1,
                        $p_category_data['properties']['pos'][C__DATA__VALUE],
                        $p_category_data['properties']['insertion'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['option'][C__DATA__VALUE],
                        $l_coord,
                        null,
                        $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE]
                    );

                    return $p_category_data['data_id'];
                }
            }
        }

        return false;
    }

    /**
     * This method handles the inheritage between location and logical location. See ID-4425
     *
     * @param bool $inherit
     * @param int  $objectTypeID
     * @param null $catentryID
     * @param null $objectID
     * @param null $parentObject
     * @param null $oldParentObject
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function handle_location_inheritage(
        $inherit = true,
        $objectTypeID = null,
        $catentryID = null,
        $objectID = null,
        $parentObject = null,
        $oldParentObject = null,
        $sourceId = null
    ) {
        if ($sourceId === null && defined('C__LOGBOOK_SOURCE__INTERNAL')) {
            $sourceId = C__LOGBOOK_SOURCE__INTERNAL;
        }
        // In case this method has been called from Addons
        if (!isys_tenantsettings::get('cmdb.logical-location.handle-location-inheritage', false)) {
            return;
        }

        if ($objectID) {
            if (!$inherit) {
                $parentObject = null;
                $l_changes = [
                    'isys_cmdb_dao_category_g_location::parent' => [
                        'from' => $this->obj_get_title_by_id_as_string($oldParentObject),
                        'to'   => ''
                    ]
                ];
            } else {
                $l_changes = [
                    'isys_cmdb_dao_category_g_location::parent' => [
                        'from' => ($oldParentObject ? $this->obj_get_title_by_id_as_string($oldParentObject) : ''),
                        'to'   => $this->obj_get_title_by_id_as_string($parentObject)
                    ]
                ];
            }

            if ($catentryID) {
                $this->save($catentryID, $objectID, $parentObject);
            } else {
                $this->create($objectID, $parentObject);
            }

            $l_event_manager = isys_event_manager::getInstance();
            $l_changes_compressed = serialize($l_changes);

            $l_event_manager->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                '',
                $objectID,
                $objectTypeID,
                'LC__CMDB__CATG__LOCATION',
                $l_changes_compressed,
                null,
                null,
                null,
                null,
                1,
                $sourceId
            );
        }
    }

    /**
     * Set cache files which have to be removed
     *
     * @return void
     */
    private function setCacheFilesToRemove()
    {
        if (empty(self::$invalidateCache)) {
            self::$invalidateCache = isys_caching::find('auth-*');
        }
    }

    /**
     * Constructor.
     *
     * @param   isys_component_database &$p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct(isys_component_database $p_db)
    {
        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.beforeCategoryEntrySave', [$this, 'validate_before_save'])
            ->connect('mod.cmdb.beforeCreateCategoryEntry', [$this, 'validate_before_save'])
            ->connect('mod.cmdb.afterCategoryEntrySave', [$this, 'validate_after_save'])
            ->connect('mod.cmdb.afterCreateCategoryEntry', [$this, 'validate_after_save'])
            ->connect('mod.cmdb.beforeUpdateLocationNode', [$this, 'update_location_node']);

        return parent::__construct($p_db);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        try {
            if (!empty(self::$invalidateCache)) {
                array_map(function ($l_cache) {
                    $l_cache->clear();
                }, self::$invalidateCache);
            }
        } catch (Exception $e) {
            isys_notify::warning(sprintf('Could not clear cache files for %sauth-* with message: ' . $e->getMessage(), isys_glob_get_temp_dir()));
        }
    }
}
