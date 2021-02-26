<?php
namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Exception\Exception;
use isys_import_handler_cmdb;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SynchronizerEntry extends AbstractSynchronizer implements SynchronizerInterface
{
    /**
     * @return $this|mixed
     */
    public function mapSyncData()
    {
        if (is_numeric($this->entryId)) {
            $this->syncData[self::ENTRY__DATA__ID] = $this->entryId;
        }

        foreach ($this->entryData as $attributeKey => $attributeValue) {
            $propertyKey = substr($attributeKey, strpos($attributeKey, '__') + 2);

            if (is_string($propertyKey) && isset($this->valueConverters[$propertyKey])) {
                $attributeValue = $this->valueConverters[$propertyKey]->convertValue($attributeValue);
            }

            $this->syncData[self::ENTRY__PROPERTIES][$propertyKey] = [C__DATA__VALUE => $attributeValue];
        }

        return $this;
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function synchronize()
    {
        if (is_numeric($this->entryId)) {
            // Update
            $type = isys_import_handler_cmdb::C__UPDATE;
        }

        if ($this->entryId === 'new') {
            // Create
            $type = isys_import_handler_cmdb::C__CREATE;
        }
        $this->merger->merge($this);

        try {
            if ($this->validateSyncData()) {
                $syncData = $this->getSyncData();
                $syncValue = $this->categoryDao->sync($syncData, $this->getObjectId(), $type);
                $this->synchronizeSuccess = true;

                \isys_component_signalcollection::get_instance()
                    ->emit('mod.cmdb.afterCategoryEntrySave', $this->categoryDao, $syncValue, true, $this->objectId, $syncData, $this->getEntryChanges());
            }
        } catch (\isys_exception_validation $validationException) {
            // This is if the entry should not be saved
            $this->synchronizeSuccess = false;
        }
    }
}
