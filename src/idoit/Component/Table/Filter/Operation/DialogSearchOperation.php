<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_cmdb_dao_list_objects;
use isys_smarty_plugin_f_dialog;

class DialogSearchOperation extends PropertyOperation
{
    /**
     * @var OperationInterface
     */
    private $fallbackOperation;

    /**
     * DialogSearchOperation constructor.
     *
     * @param OperationInterface $fallbackOperation
     */
    public function __construct(OperationInterface $fallbackOperation)
    {
        $this->fallbackOperation = $fallbackOperation;
    }

    public function isApplicable($filter, $value)
    {
        $property = $this->getProperty($filter);

        return $property &&
            isset($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]) &&
            isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT])
            && in_array(
                $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                [C__PROPERTY__INFO__TYPE__DIALOG, C__PROPERTY__INFO__TYPE__DIALOG_PLUS, C__PROPERTY__INFO__TYPE__DIALOG_LIST],
                true
            );
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     *
     * @return mixed
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value)
    {
        if (!isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT]) || !$property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] instanceof SelectSubSelect) {
            return false;
        }
        /** @var SelectSubSelect $select */
        $select = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];

        /**
         * Temporary solution to handle incorrect select query in DATA__SELECT. It has to look like 'SELECT sth FROM bla JOIN bla', but sometimes it is just 'sth'
         */
        if (strpos($select->getSelectQuery(), ' ') === false) {
            // if it looks like id - use item value
            if ((string)$value === (string)(int)$value) {
                $items = $this->getItems($property);
                $value = isset($items[$value]) ? $items[$value] : $value;
            }

            return $this->fallbackOperation->apply($listDao, $name, $value);
        }
        $idField = $select->getSelectFieldObjectID() ?: $select->getSelectReferenceKey();
        $objField = 'isys_obj__id';
        if (strpos($property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_obj__') === 0) {
            $objField = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        }
        $objField = 'obj_main.' . $objField;
        $selection = $select->getSelection();
        $field = isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) ? $property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] : $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

        $ids = [];
        // if the value looks like id of entry - add it to the ids
        if ((string)$value === (string)(int)$value) {
            $items = $this->getItems($property);
            if (is_array($items) && array_key_exists($value, $items)) {
                $searchTitle = $items[$value];
                foreach ($items as $propId => $propValue) {
                    if ($propValue == $searchTitle) {
                        $ids[] = $propId;
                    }
                }
            } else {
                $ids[] = $value;
                // @see ID-7233 the search must be on ints like on strings, it breaks on string parts loooking like ints
                // used logic for search on ints like string part
                $items = array_filter($items, function ($item) use ($value) {
                    $item = \isys_application::instance()->container->get('language')->get($item);
                    return strlen($value) > 0 && stripos($item, $value) !== false;
                }, ARRAY_FILTER_USE_BOTH);
                if (count($items)) {
                    $ids = array_merge($ids, array_keys($items));
                }
            }
        } else {
            // if it looks like string - fetch values and manualy compare values -> receive matching ids
            $items = $this->getItems($property);
            if (is_array($items)) {
                $items = array_filter($items, function ($item) use ($value) {
                    $item = \isys_application::instance()->container->get('language')->get($item);
                    return strlen($value) > 0 && stripos($item, $value) !== false;
                }, ARRAY_FILTER_USE_BOTH);
                if (count($items)) {
                    $ids = array_keys($items);
                }
            }
        }

        // @see  ID-7563  Specific handling for "yes/no" field of custom categories
        if (in_array($value, ['LC__UNIVERSAL__YES', 'LC__UNIVERSAL__NO'], true)) {
            $listDao->add_additional_having_conditions($name . ' LIKE ' . $listDao->convert_sql_text("%$value%"));

            return true;
        }

        if (empty($ids)) {
            $listDao->add_additional_conditions('AND FALSE');

            return true;
        }

        $select->setSelectQuery(str_replace($selection, 'COUNT(1)', $select->getSelectQuery()));
        if (empty($select->getSelectCondition()
            ->getCondition())) {
            $select->getSelectCondition()
                ->addCondition('TRUE');
        }
        $select->getSelectCondition()
            ->addCondition(' AND ' . $idField . ' = ' . $objField)
            ->addCondition(' AND ' . $field . ' IN (' . implode(', ', $ids) . ')');
        $field = 'f' . rand(0, 100000);
        $listDao->add_additional_selects($select, $field);
        $listDao->add_additional_having_conditions($field . ' > 0');

        return true;
    }

    /**
     * Get array data from property
     *
     * @param array|\ArrayAccess $property
     *
     * @return array|mixed
     */
    protected function getItems($property)
    {
        if (!is_array($property) && !($property instanceof \ArrayAccess)) {
            return [];
        }

        if (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']) && $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']) {
            $dialog = new isys_smarty_plugin_f_dialog();

            return $dialog->get_array_data(
                $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'],
                C__RECORD_STATUS__NORMAL,
                null,
                $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['condition']
            );
        } elseif (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
            if (is_array($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                // If we simply get an array.
                return $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
            } elseif (is_object($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                get_class($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) == 'isys_callback') {
                // If we get an instance of "isys_callback"
                return $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
            } elseif (is_string($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                // Or if we get a string (we assume it's serialized).
                return unserialize($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
            }
        }

        return [];
    }
}
