<?php

namespace idoit\Module\Search\Index\Engine;

use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\Exception\DocumentExists;
use isys_application;
use isys_component_database;
use isys_exception_database_mysql;
use MySQL\Error\Server as MySQLServerErrors;

/**
 * i-doit
 *
 * Mysql
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Mysql implements SearchEngine
{
    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * Begin transaction
     */
    public function begin()
    {
        $this->database->begin();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->database->commit();
    }
    
    /**
     * @param Document $document
     *
     * @throws \isys_exception_database_mysql
     * @throws DocumentExists
     */
    public function insertDocument(Document $document)
    {
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $version = $dao->convert_sql_int($document->getVersion());
        $type = $dao->convert_sql_text($document->getType());
        $metadata = $dao->convert_sql_text(json_encode($document->getMetadata(), JSON_UNESCAPED_UNICODE));
        $key = $dao->convert_sql_text($document->getMetadata()->__toString());
        $value = $dao->convert_sql_text($document->getValue());
        $reference = $dao->convert_sql_int($document->getReference());

        $sql = "INSERT INTO isys_search_idx SET
            isys_search_idx__version = {$version},
            isys_search_idx__type = {$type},
            isys_search_idx__metadata = {$metadata},
            isys_search_idx__key = {$key},
            isys_search_idx__value = {$value},
            isys_search_idx__reference = {$reference}
        
            ON DUPLICATE KEY UPDATE
            isys_search_idx__type = {$type},
            isys_search_idx__metadata = {$metadata},
            isys_search_idx__value = {$value},
            isys_search_idx__reference = {$reference};";

        try {
            $this->database->query($sql);
        } catch (isys_exception_database_mysql $exception) {
            if ($exception->getCode() === MySQLServerErrors::ER_DUP_ENTRY) {
                throw new DocumentExists('');
            }

            if ($exception->getCode() === MySQLServerErrors::ER_LOCK_WAIT_TIMEOUT) {
                return;
            }

            throw $exception;
        }
    }

    /**
     * @param Document $document
     */
    public function updateDocument(Document $document)
    {
        try {
            $sql = sprintf(
                'UPDATE isys_search_idx SET isys_search_idx__metadata=\'%s\', isys_search_idx__key="%s", isys_search_idx__value="%s" 
                        WHERE isys_search_idx__version = 1 AND isys_search_idx__key="%s";',
                json_encode($document->getMetadata()),
                $this->database->escape_string($document->getMetadata()->__toString()),
                $this->database->escape_string($document->getValue()),
                $document->getMetadata()->__toString()
            );
            $this->database->query($sql);
        } catch (isys_exception_database_mysql $exception) {
            if ($exception->getCode() === MySQLServerErrors::ER_LOCK_WAIT_TIMEOUT) {
                return;
            }

            throw $exception;
        }
    }

    /**
     * @param Document $document
     */
    public function deleteDocument(Document $document)
    {
        try {
            $deleteSql = sprintf(
                'DELETE FROM isys_search_idx WHERE isys_search_idx__version = 1 AND 
                  isys_search_idx__key = \'%s\' AND isys_search_idx__reference = \'%s\';',
                $document->getKey(),
                $document->getReference()
            );

            $this->database->query($deleteSql);
        } catch (isys_exception_database_mysql $exception) {
            if ($exception->getCode() === MySQLServerErrors::ER_LOCK_WAIT_TIMEOUT) {
                return;
            }

            throw $exception;
        }
    }

    /**
     * Retrieves unique document references
     *
     * @return int[]
     */
    public function retrieveUniqueDocumentReferences()
    {
        $references = [];

        $documentReferences = $this->database
            ->retrieveArrayFromResource($this->database
                ->query('SELECT DISTINCT isys_search_idx__reference FROM isys_search_idx;'));

        foreach ($documentReferences as $reference) {
            $references[] = (int)$reference['isys_search_idx__reference'];
        }

        return $references;
    }

    /**
     * Truncates index table
     */
    public function clearIndex()
    {
        $this->database->query('TRUNCATE TABLE isys_search_idx;');
    }

    /**
     * @param string
     *
     * @return void
     */
    public function deleteByWildcard($wildcard)
    {
        try {
            $this->database->query('DELETE FROM isys_search_idx WHERE isys_search_idx__version = 1 AND isys_search_idx__key LIKE "' . $wildcard . '"');
        } catch (isys_exception_database_mysql $exception) {
            if ($exception->getCode() === MySQLServerErrors::ER_LOCK_WAIT_TIMEOUT) {
                return;
            }

            throw $exception;
        }
    }
    
    /**
     * @param isys_component_database $database
     */
    public function __construct(
        isys_component_database $database
    ) {
        $this->database = $database;
    }
}
