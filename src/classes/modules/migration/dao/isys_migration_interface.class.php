<?php

/**
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_migration_interface
{
    /**
     * @return string
     */
    public function getMigrationTitle();

    /**
     * @return bool
     */
    public function migrationAlreadyExecuted();

    /**
     * @return bool
     */
    public function deactivateMigration();

    public function executeMigration();
}
