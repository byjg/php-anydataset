<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\DbFunctionsInterface;
use ByJG\AnyDataset\Database\DbOci8Driver;
use ByJG\AnyDataset\Database\DbPdoDriver;
use ByJG\AnyDataset\Database\DbSqlRelayDriver;
use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\Util\Uri;
use PDO;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class DBDataset
 * @todo Review projects with Anydata dependency
 * @package ByJG\AnyDataset\Repository
 */
class DBDataset
{

    /**
     * Enter description here...
     *
     * @var Uri
     */
    protected $connectionUri;

    /**

     * @var DbDriverInterface
     */
    private $dbDriver = null;


    /**
     * @var CacheEngineInterface
     */
    protected $cacheEngine;

    /**
     * @param string $connectionString Uri of the connection string.
     */
    public function __construct($connectionString)
    {
        $this->connectionUri = new Uri($connectionString);

        // Create the proper driver
        if ($this->connectionUri->getScheme() == "sqlrelay") {
            $this->dbDriver = new DbSqlRelayDriver($this->connectionUri);
        } elseif ($this->connectionUri->getScheme() == "oci8") {
            $this->dbDriver = new DbOci8Driver($this->connectionUri->__toString());
        } else {
            $this->dbDriver = DbPdoDriver::factory($this->connectionUri);
        }
    }

    /**
     * @return Uri
     */
    public function getConnectionUri()
    {
        return $this->connectionUri;
    }

    public function testConnection()
    {
        return true;
    }


    /**
     * @todo Remove dependency on ByJG/Cache-Engine and to psr/cache
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function setCacheEngine(CacheItemPoolInterface $cache)
    {
        $this->cacheEngine = $cache;
    }

    /**
     * @return CacheItemPoolInterface
     * @todo Think another way to this.
     * @throws NotAvailableException
     */
    public function getCacheEngine()
    {
        if (is_null($this->cacheEngine)) {
            throw new NotAvailableException('Cache Engine not available');
        }
        return $this->cacheEngine;
    }

    /**
     * Get the DBDriver
     * @return DbDriverInterface
     */
    public function getDbDriver()
    {
        return $this->dbDriver;
    }

    /**
     * @access public
     * @param string $sql
     * @param array $params
     * @param int $ttl
     * @return IteratorInterface
     * @throws NotAvailableException
     */
    public function getIterator($sql, $params = null, $ttl = null)
    {
        // If there is no TTL query, return the LIVE iterator
        if (empty($ttl)) {
            return $this->getDbDriver()->getIterator($sql, $params);
        }

        // Otherwise try to get from cache
        $key = $this->getQueryKey($sql, $params);

        // Get the CACHE
        // @todo Analyse that
        $cacheItem = $this->getCacheEngine()->getItem($key);
        if (!$cacheItem->isHit()) {
            $iterator = $this->getDbDriver()->getIterator($sql, $params);

            $cacheItem->set($iterator->toArray());
            $cacheItem->expiresAfter(new \DateInterval("$ttl seconds"));

            $this->getCacheEngine()->save($key, $cacheItem);
        }

        $arrayDS = new ArrayDataset($cacheItem);
        return $arrayDS->getIterator();
    }

    protected function getQueryKey($sql, $array)
    {
        $key1 = md5($sql);

        // Check which parameter exists in the SQL
        $arKey2 = array();
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (preg_match("/\[\[$key\]\]/", $sql)) {
                    $arKey2[$key] = $value;
                }
            }
        }

        // Define the query key
        if (is_array($arKey2) && count($arKey2) > 0) {
            $key2 = ":" . md5(json_encode($arKey2));
        } else {
            $key2 = "";
        }

        return  "qry:" . $key1 . $key2;
    }

    public function getScalar($sql, $array = null)
    {
        return $this->getDbDriver()->getScalar($sql, $array);
    }

    /**
     * @access public
     * @param string $tablename
     * @return array
     */
    public function getAllFields($tablename)
    {
        return $this->getDbDriver()->getAllFields($tablename);
    }

    /**
     * @access public
     * @param string $sql
     * @param array $array
     * @return Resource
     */
    public function execSQL($sql, $array = null)
    {
        $this->getDbDriver()->executeSql($sql, $array);
    }

    public function beginTransaction()
    {
        $this->getDbDriver()->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->getDbDriver()->commitTransaction();
    }

    public function rollbackTransaction()
    {
        $this->getDbDriver()->rollbackTransaction();
    }

    /**
     * @access public
     * @param IteratorInterface $iterator
     * @param string $fieldPK
     * @param string $fieldName
     * @return Resource
     */
    public function getArrayField(IteratorInterface $iterator, $fieldPK, $fieldName)
    {
        $result = array();
        while ($iterator->hasNext()) {
            $registro = $iterator->moveNext();
            $result [$registro->getField($fieldPK)] = $registro->getField($fieldName);
        }
        return $result;
    }

    /**
     * @access public
     * @return PDO
     */
    public function getDBConnection()
    {
        return $this->getDbDriver()->getDbConnection();
    }

    /**

     * @var DbFunctionsInterface
     */
    protected $dbFunction = null;

    /**
     * Get a IDbFunctions class to execute Database specific operations.
     *
     * @return DbFunctionsInterface
     */
    public function getDbFunctions()
    {
        if (is_null($this->dbFunction)) {
            $dbFunc = "\\ByJG\\AnyDataset\\Database\\Expressions\\Db"
                . ucfirst($this->connectionUri->getScheme())
                . "Functions";
            $this->dbFunction = new $dbFunc();
        }

        return $this->dbFunction;
    }

    public function setDriverAttribute($name, $value)
    {
        return $this->getDbDriver()->setAttribute($name, $value);
    }

    public function getDriverAttribute($name)
    {
        return $this->getDbDriver()->getAttribute($name);
    }
}
