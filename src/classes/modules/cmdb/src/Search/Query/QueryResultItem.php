<?php

namespace idoit\Module\Cmdb\Search\Query;

use idoit\Module\Cmdb\Model\CiType;
use idoit\Module\Cmdb\Model\CiTypeCache;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Query\AbstractQueryResultItem;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResultItem as QueryResultItemProtocol;
use isys_application;
use isys_tenantsettings;

/**
 * i-doit
 *
 * Default query result item
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class QueryResultItem extends AbstractQueryResultItem implements QueryResultItemProtocol, \JsonSerializable
{
    /**
     * @var string
     */
    protected $type = 'cmdb';

    /**
     * @var array
     */
    protected $keys;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var string
     */
    private $objectStatus;

    /**
     * @var CiType[]
     */
    private static $ciTypeCache = null;

    /**
     * @return string
     */
    public function getKey()
    {
        if (!self::$ciTypeCache) {
            self::$ciTypeCache = CiTypeCache::instance(\isys_application::instance()->database)
                ->getCiTypes();
        }

        $language = isys_application::instance()->container->get('language');

        if (stripos($this->getDocumentMetadata()->getCategoryDao(), 'custom_fields') !== false) {
            return sprintf(
                '%s > %s',
                self::$ciTypeCache[$this->getDocumentMetadata()->getObjectTypeId()],
                str_replace('_', ' ', $language->get($this->getDocumentMetadata()->getCategoryTitle()))
            );
        }

        return sprintf(
            '%s > %s > %s',
            self::$ciTypeCache[$this->getDocumentMetadata()->getObjectTypeId()],
            $language->get($this->getDocumentMetadata()->getCategoryTitle()),
            $language->get_in_text($this->getDocumentMetadata()->getPropertyTitle())
        );
    }

    /**
     * @return string
     */
    public function getLink()
    {
        $categoryAddition = '';
        $class = $this->getDocumentMetadata()->getCategoryDao();

        /**
         * @var $instance \isys_cmdb_dao_category
         */
        $instance = call_user_func([
            $class,
            'instance'
        ], \isys_application::instance()->database);

        if ($instance === null) {
            return rtrim(\isys_application::instance()->www_path, '/') . '/?objID=' . $this->getDocumentId();
        }

        $categoryAddition = '&' . $instance->get_category_type_abbr() . 'ID=' . $instance->get_category_id() . '&cateID=' . $this->categoryId;

        if ($instance instanceof \isys_cmdb_dao_category_g_custom_fields && method_exists($instance, 'get_catg_custom_id')) {
            if (defined($this->getDocumentMetadata()->getCategoryConstant())) {
                $categoryAddition .= '&customID=' . constant($this->getDocumentMetadata()->getCategoryConstant());
            } else {
                // Fallback: just link to the object.
                $categoryAddition = '';
            }
        }

        if (!$categoryAddition) {
            if (stripos($this->getDocumentMetadata()->getCategoryConstant(), 'C__CATG__') !== false) {
                if (stripos($this->getDocumentMetadata()->getCategoryDao(), 'custom_fields') !== false) {
                    $categoryAddition = '&' . C__CMDB__GET__CATG . '=' . C__CATG__CUSTOM_FIELDS .
                        '&' . C__CMDB__GET__CATG_CUSTOM . '=' . constant($this->getDocumentMetadata()->getCategoryConstant()) .
                        '&' . C__CMDB__GET__CATLEVEL . '=' . $this->categoryId;
                } else {
                    $categoryAddition = '&' . C__CMDB__GET__CATG . '=' . constant($this->getDocumentMetadata()->getCategoryConstant()) .
                        '&' . C__CMDB__GET__CATLEVEL . '=' . $this->categoryId;
                }
            } elseif (stripos($this->getDocumentMetadata()->getCategoryConstant(), 'C__CATS__') !== false) {
                $categoryAddition = '&catsID=' . constant($this->getDocumentMetadata()->getCategoryConstant()) . '&cateID=' . $this->categoryId;
            } elseif (defined('C__CATG__NETWORK_PORT') && stripos($this->getDocumentMetadata()->getCategoryDao(), 'network_port') !== false) {
                $categoryAddition = '&catgID=' . C__CATG__NETWORK_PORT . '&cateID=' . $this->categoryId;
            } elseif (defined('C__CATG__STORAGE_DEVICE') && stripos($this->getDocumentMetadata()->getCategoryDao(), 'storage_device') !== false) {
                $categoryAddition = '&catgID=' . C__CATG__STORAGE_DEVICE . '&cateID=' . $this->categoryId;
            } else {
                $categoryAddition = '';
            }
        }

        if (isset($this->conditions[0]) && (bool)isys_tenantsettings::get('search.highlight-search-string', 1)) {
            $categoryAddition .= '&highlight=' . urlencode($this->conditions[0]);
        }

        return rtrim(\isys_application::instance()->www_path, '/') . '/?objID=' . $this->getDocumentId() . $categoryAddition;
    }

    /**
     * JsonSerializable Interface
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'documentId' => $this->getDocumentId(),
            'key'        => $this->getKey(),
            'value'      => $this->getValue(),
            'type'       => $this->getType(),
            'link'       => $this->getLink(),
            'score'      => $this->getScore(),
            'status' => $this->getStatus()
        ];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if (!isys_tenantsettings::get('search.index.include_archived_deleted_objects', false)) {
            return isys_application::instance()->container->get('language')->get('LC__CMDB__RECORD_STATUS__NORMAL');
        }
        $recordStatus = $this->getDocumentMetadata()->getCategoryStatus() ?: $this->getDocumentMetadata()->getObjectStatus();
        return isys_application::instance()->container->get('cmdb_dao')->get_record_status_as_string($recordStatus);
    }

    /**
     * QueryResultItem constructor.
     *
     * @param DocumentMetadata $documentMetadata
     * @param int              $documentId
     * @param string           $key
     * @param string           $value
     * @param double           $score
     * @param Condition[]      $conditions
     */
    public function __construct(DocumentMetadata $documentMetadata, $documentId, $key, $value, $score, array $conditions)
    {
        parent::__construct($documentMetadata, $documentId, $key, $value, $score, $conditions);

        $this->keys = $documentMetadata->__toString();
        $this->categoryId = $documentMetadata->getCategoryId();
    }
}
