<?php

/**
 * @var $g_comp_database isys_component_database
 */
global $g_comp_database, $g_absdir, $g_mandator_info, $g_comp_database_system;

// Set migrationId
$migrationIdentifier = "layer2-definition-migration";

// Check whether migration was executed before
if ($this->is_migration_done($migrationIdentifier)) {
    $g_migration_log[] = '<span class="bold">Migration of layer2 definitions has already been done.</span>';
} else {
    $g_migration_log[] = '<span class="bold">Starting Migration of layer2 definitions.</span>';
    $migration = new isys_update_migration();

    $foreignKeys = [];
    if (($foreignKeyField = $migration->get_foreign_key('isys_cats_layer2_net_2_layer3', 'isys_cats_layer2_net_list__id'))) {
        $foreignKeys['isys_cats_layer2_net_list__id'] = $foreignKeyField;
    }

    if (($foreignKeyField = $migration->get_foreign_key('isys_cats_layer2_net_2_layer3', 'isys_obj__id'))) {
        $foreignKeys['isys_obj__id'] = $foreignKeyField;
    }

    // 1. DROP FOREIGN KEYS
    if (!empty($foreignKeys)) {
        $foreignKeyDropQuery = "ALTER TABLE isys_cats_layer2_net_2_layer3 DROP FOREIGN KEY %s;";
        foreach ($foreignKeys as $foreignKey) {
            $g_comp_database->query(sprintf($foreignKeyDropQuery, $foreignKey));
        }
    }

    // 2. DROP PRIMARY KEY and Add new Primary Key
    $result = $g_comp_database->query('SHOW KEYS FROM isys_cats_layer2_net_2_layer3 WHERE Key_name = \'PRIMARY\';');
    if ($g_comp_database->num_rows($result) > 1) {
        $g_comp_database->query('ALTER TABLE isys_cats_layer2_net_2_layer3 DROP PRIMARY KEY;');
        $g_comp_database->query('ALTER TABLE isys_cats_layer2_net_2_layer3 ADD isys_cats_layer2_net_2_layer3__id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY;');
    }

    // 3. READD FOREIGN KEYS
    if (!empty($foreignKeys)) {
        $foreignKeyDropQuery = "ALTER TABLE isys_cats_layer2_net_2_layer3 ADD FOREIGN KEY (%s) REFERENCES %s (%s) ON UPDATE CASCADE ON DELETE CASCADE;";
        foreach (array_keys($foreignKeys) as $foreignKey) {
            $g_comp_database->query(sprintf($foreignKeyDropQuery, $foreignKey, str_replace('__id', '', $foreignKey), $foreignKey));
        }
    }

    // 4. ADD RELATION FIELD WITH FOREIGN KEY
    $result = $g_comp_database->query('SHOW FIELDS FROM isys_cats_layer2_net_2_layer3
        WHERE FIELD LIKE \'isys_cats_layer2_net_2_layer3__isys_catg_relation_list__id\';');
    if ($g_comp_database->num_rows($result) === 0) {
        $addField = 'ALTER TABLE isys_cats_layer2_net_2_layer3 ADD isys_cats_layer2_net_2_layer3__isys_catg_relation_list__id int(10) unsigned NULL DEFAULT NULL;';
        $g_comp_database->query($addField);

        $addField = 'ALTER TABLE isys_cats_layer2_net_2_layer3 ADD FOREIGN KEY (isys_cats_layer2_net_2_layer3__isys_catg_relation_list__id) 
            REFERENCES isys_catg_relation_list (isys_catg_relation_list__id) ON UPDATE CASCADE ON DELETE SET NULL;';
        $g_comp_database->query($addField);
    }

    if ($g_comp_database->commit()) {
        // Create relation objects
        $relationDao = isys_cmdb_dao_category_g_relation::instance($g_comp_database);
        $result = $relationDao->retrieve('SELECT * FROM isys_cats_layer2_net_2_layer3 conn INNER JOIN
            isys_cats_layer2_net_list cats ON cats.isys_cats_layer2_net_list__id = conn.isys_cats_layer2_net_list__id;');
        if (count($result)) {
            while ($row = $result->get_row()) {
                $relationDao->create_relation(
                    'isys_cats_layer2_net_2_layer3',
                    $row['isys_cats_layer2_net_2_layer3__id'],
                    $row['isys_obj__id'],
                    $row['isys_cats_layer2_net_list__isys_obj__id'],
                    'C__RELATION_TYPE__LAYER3_2_LAYER2'
                );
            }
        }
    }

    $g_migration_log[] = '<span class="bold">Migration finished!</span>';

    $this->migration_done($migrationIdentifier);
}
