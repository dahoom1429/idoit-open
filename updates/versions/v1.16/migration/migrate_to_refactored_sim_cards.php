<?php

/**
 * i-doit - Updates
 *
 * Migrating data to the refactored sim cards
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * @var $g_comp_database isys_component_database
 */
global $g_comp_database, $g_absdir, $g_mandator_info, $g_comp_database_system;

// Set migrationId
$g_migration_identifier = "migrate_to_refactored_sim_cards";

// Check whether migration was executed before
if ($this->is_migration_done($g_migration_identifier)) {
    $g_migration_log[] = '<span class="bold">Migration of sim cards data already done.</span>';
} else {
    $g_migration_log[] = '<span class="bold">Starting Migration of sim cards data.</span>';

    isys_application::instance()->container->get('database')->setDatabase($g_comp_database);
    $dao = isys_cmdb_dao_category_g_assigned_sim_cards::instance(isys_application::instance()->container->get('database'));

    $resource = $g_comp_database->query('SELECT * FROM isys_catg_cards_list_2_isys_obj AS conn
        INNER JOIN isys_catg_cards_list AS cat ON cat.isys_catg_cards_list__id = conn.isys_catg_cards_list__id
        INNER JOIN isys_obj AS obj ON obj.isys_obj__id = conn.isys_obj__id');

    $data = [];
    while ($row = $g_comp_database->fetch_row_assoc($resource)) {
        $data[$row['isys_obj__id']][] = $row['isys_catg_cards_list__id'];
    }
    foreach ($data as $objId => $assignedCards) {
        $uniqueCards = array_unique($assignedCards);
        foreach ($uniqueCards as $cardId) {
            $dao->save($objId, $cardId);
        }
    }

    $g_migration_log[] = '<span class="bold">Migration finished!</span>';

    $this->migration_done($g_migration_identifier);
}
