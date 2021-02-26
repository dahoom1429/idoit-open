<?php

namespace idoit\Module\System\Model;

use idoit\Model\Dao\Base;
use isys_component_dao_result;
use isys_exception_database;

/**
 * Class RelationType
 *
 * @package   idoit\Module\System\Model
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class RelationType extends Base
{
    const FIELDS = [
        'isys_relation_type__id'                 => 'id',
        'isys_relation_type__title'              => 'title',
        'isys_relation_type__master'             => 'titleMaster',
        'isys_relation_type__slave'              => 'titleSlave',
        'isys_relation_type__type'               => 'type',
        'isys_relation_type__default'            => 'default',
        'isys_relation_type__const'              => 'constant',
        'isys_relation_type__category'           => 'category',
        'isys_relation_type__editable'           => 'editable',
        'isys_relation_type__sort'               => 'sort',
        'isys_relation_type__status'             => 'status',
        'isys_relation_type__isys_weighting__id' => 'weightingId',
    ];

    /**
     * @param int $id
     *
     * @return isys_component_dao_result
     * @throws \isys_exception_database
     */
    public function getById(int $id): isys_component_dao_result
    {
        $selects = $this->selectImplode(self::FIELDS);

        return $this->retrieve("SELECT {$selects} FROM isys_relation_type WHERE isys_relation_type__id = {$id};");
    }

    /**
     * @param int|null $id
     * @param array    $data
     *
     * @return int
     * @throws \isys_exception_dao
     */
    public function save(int $id = null, array $data): int
    {
        $fieldValues = [];
        $fieldKeys = array_flip(self::FIELDS);

        // Just to go sure the ID will not be overwritten.
        unset($data['id']);

        if ($id === null) {
            // If we create a new relation type we definitely want to add a constant.
            if (!isset($data['constant']) || empty($data['constant'])) {
                $data['constant'] = $this->generateConstant($data['title']);
            }

            // Also position the new relation type at the bottom, if no specific sorting has been provided.
            if (!isset($data['sort']) || empty ($data['sort'])) {
                $data['sort'] = $this->retrieve('SELECT MAX(isys_relation_type__sort) AS sort FROM isys_relation_type;')->get_row_value('sort') + 1;
            }
        } else {
            // Constants can ONLY be set during creation.
            unset($data['constant']);
        }

        foreach ($data as $key => $value) {
            if (!isset($fieldKeys[$key])) {
                continue;
            }

            switch ($key) {
                default:
                    $value = $this->convert_sql_text($value);
                    break;

                case 'editable':
                    $value = $this->convert_sql_boolean($value);
                    break;

                case 'weightingId':
                    $value = $this->convert_sql_id($value);
                    break;

                case 'default':
                case 'sort':
                case 'status':
                    $value = $this->convert_sql_int($value);
                    break;
            }

            $fieldValues[] = $fieldKeys[$key] . ' = ' . $value;
        }

        $values = implode(', ', $fieldValues);

        if ($id === null) {
            // Create context.
            $this->update("INSERT INTO isys_relation_type SET {$values};");

            // Get the last created ID.
            $id = $this->get_last_insert_id();
        } else {
            // Update context.
            $this->update("UPDATE isys_relation_type SET {$values} WHERE isys_relation_type__id = {$id};");
        }

        return $id;
    }

    /**
     * @param int|array $ids
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    public function delete($ids): bool
    {
        $idCondition = $this->prepare_in_condition((array)$ids);

        return $this->update("DELETE FROM isys_relation_type WHERE isys_relation_type__id {$idCondition};") && $this->apply_update();
    }

    /**
     * Will create a (custom) relation type constant.
     *
     * @param string $title
     *
     * @return string
     * @throws isys_exception_database
     */
    public function generateConstant(string $title): string
    {
        $constant = strtoupper($title);
        $constant = preg_replace('~(\s|-)+~', '_', $constant);
        $constant = preg_replace('~_{2,}~', '_', $constant);
        $constant = preg_replace('~[^a-z0-9_]~i', '', $constant);

        $counter = 1;
        $finalConstant = 'C__RELATION_TYPE_CUSTOM__' . $constant;

        // Find a valid constant, that has not yet been taken.
        while (!$this->isConstantAvailable($finalConstant)) {
            $finalConstant = 'C__RELATION_TYPE_CUSTOM__' . $constant . '_' . $counter;
            $counter++;

            if ($counter > 10) {
                break;
            }
        }

        return $finalConstant;
    }

    /**
     * @param string $constant
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function isConstantAvailable(string $constant): bool
    {
        $sql = 'SELECT isys_relation_type__id 
            FROM isys_relation_type 
            WHERE isys_relation_type__const = ' . $this->convert_sql_text($constant) . '
            LIMIT 1;';

        return $this->retrieve($sql)->get_row_value('isys_relation_type__id') === null;
    }
}
