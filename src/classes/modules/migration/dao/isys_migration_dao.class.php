<?php

abstract class isys_migration_dao extends isys_module_dao
{
    public function get_data()
    {
        // TODO: Implement get_data() method.
    }

    /**
     * Creates an entry in isys_migration
     *
     * @param string $title
     * @param string $executed_by
     * @param string $context
     * @param string $version
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function addMigrationEntry($title, $executed_by = 'system', $context = 'migration_by_user', $version = null)
    {
        if ($version === null) {
            $version = isys_application::instance()->info->get('version');
        }

        $query = 'INSERT INTO isys_migration SET 
            isys_migration__title = ' . $this->convert_sql_text($title) . ',
            isys_migration__version = ' . $this->convert_sql_text($version) . ',
            isys_migration__executed_on = ' . $this->convert_sql_datetime('NOW()') . ',
            isys_migration__executed_by = ' . $this->convert_sql_text($executed_by). ',
            isys_migration__context = ' . $this->convert_sql_text($context);

        return $this->update($query) && $this->apply_update();
    }
}
