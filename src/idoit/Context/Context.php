<?php

namespace idoit\Context;

use idoit\Component\Provider\Singleton;

class Context
{
    use Singleton;

    const ORIGIN_GUI     = 'gui';
    const ORIGIN_API     = 'api';
    const ORIGIN_CONSOLE = 'console';

    const CONTEXT_GROUP_IMPORT    = 'import';
    const CONTEXT_GROUP_EXPORT    = 'export';
    const CONTEXT_GROUP_DUPLICATE = 'duplicate';
    const CONTEXT_GROUP_MULTIEDIT = 'multiedit';
    const CONTEXT_GROUP_DAO       = 'dao';
    const CONTEXT_GROUP_OBJECT    = 'object';
    const CONTEXT_GROUP_TEMPLATE  = 'template';

    const CONTEXT_IMPORT_CSV   = 'import_csv';
    const CONTEXT_IMPORT_JDISC = 'import_jdisc';
    const CONTEXT_IMPORT_OCS   = 'import_ocs';
    const CONTEXT_IMPORT_XML   = 'import_xml';

    const CONTEXT_LDAP_SYNC   = 'ldap_sync';
    const CONTEXT_LDAP_IMPORT = 'ldap_import';

    const CONTEXT_DUPLICATE = 'duplicate';
    const CONTEXT_MULTIEDIT = 'multiedit';

    const CONTEXT_DAO_UPDATE = 'dao_update';
    const CONTEXT_DAO_CREATE = 'dao_create';

    const CONTEXT_OBJECT_CREATE = 'object_create';

    const CONTEXT_TEMPLATE    = 'template';
    const CONTEXT_MASS_CHANGE = 'mass_change';

    const CONTEXT_EXPORT_XML = 'export_xml';
    const CONTEXT_EXPORT_PRINTVIEW = 'export_printview';

    const CONTEXT_RANK_OBJECT   = 'object_rank';
    const CONTEXT_RANK_CATEGORY = 'category_rank';

    const CONTEXT_RANK_CATEGORY_DELETED  = 'category_deleted';
    const CONTEXT_RANK_CATEGORY_PURGED   = 'category_purged';
    const CONTEXT_RANK_CATEGORY_ARCHIVED = 'category_archived';
    const CONTEXT_RANK_CATEGORY_RECYCLED = 'category_recycled';

    const CONTEXT_RANK_OBJECT_DELETED  = 'object_deleted';
    const CONTEXT_RANK_OBJECT_PURGED   = 'object_purged';
    const CONTEXT_RANK_OBJECT_ARCHIVED = 'object_archived';
    const CONTEXT_RANK_OBJECT_RECYCLED = 'object_recycled';

    const CONTEXT_OBJECT_TYPE_PURGE = 'object_type_purge';
    const CONTEXT_OBJECT_TYPE_SAVE  = 'object_type_save';

    const CONTEXT_MIGRATION         = 'migration';
    const CONTEXT_MIGRATION_BY_USER = 'migration_by_user';
    const CONTEXT_MIGRATION_BY_UPDATE = 'migration_by_update';
    const CONTEXT_MIGRATION_BY_INSTALL = 'migration_by_install';

    const CONTEXT_REPORT = 'report';
    const CONTEXT_REPORT_SELECTION = 'report_selection';
    const CONTEXT_REPORT_CONDITION = 'report_condition';

    /**
     * E.g. csv_import
     *
     * @var string
     */
    private $contextTechnical;

    /**
     * E.g. technical context is import, and context customer is duplicate
     *
     * @var string
     */
    private $contextCustomer;

    /**
     * E.g. import when executing csv import
     *
     * @var string
     */
    private $group;

    /**
     * E.g. api or console
     *
     * @var string
     */
    private $origin;

    /**
     * If context should be immutable
     *
     * @var bool
     */
    private $immutable = false;

    /**
     * @param bool $immutable
     *
     * @return $this
     */
    public function setImmutable($immutable)
    {
        $this->immutable = $immutable;

        return $this;
    }

    /**
     * @return string
     */
    public function getContextTechnical()
    {
        return $this->contextTechnical;
    }

    /**
     * @param string $contextTechnical
     *
     * @return Context
     */
    public function setContextTechnical($contextTechnical)
    {
        if ($this->immutable) {
            return $this;
        }

        $this->contextTechnical = $contextTechnical;

        return $this;
    }

    /**
     * @return string
     */
    public function getContextCustomer()
    {
        return $this->contextCustomer;
    }

    /**
     * @param string $contextCustomer
     *
     * @return Context
     */
    public function setContextCustomer($contextCustomer)
    {
        if ($this->immutable) {
            return $this;
        }

        $this->contextCustomer = $contextCustomer;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     *
     * @return Context
     */
    public function setGroup($group)
    {
        if ($this->immutable) {
            return $this;
        }

        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     *
     * @return Context
     */
    public function setOrigin($origin)
    {
        if ($this->immutable) {
            return $this;
        }

        $this->origin = $origin;

        return $this;
    }
}
