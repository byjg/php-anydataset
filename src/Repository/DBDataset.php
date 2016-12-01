<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Database\DBDriverInterface;
use ByJG\AnyDataset\Database\DbFunctionsInterface;
use ByJG\AnyDataset\Database\DBOci8Driver;
use ByJG\AnyDataset\Database\DBPDODriver;
use ByJG\AnyDataset\Database\DBSQLRelayDriver;
use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\Cache\CacheEngineInterface;
use PDO;

class DBDataset
{

    /**
     * Enter description here...
     *
     * @var ConnectionManagement
     */
    protected $_connectionManagement;

    /**
     *
     * @var DBDriverInterface
     */
    private $_dbDriver = null;


    /**
     * @var CacheEngineInterface
     */
    protected $_cacheEngine;

    /**
     * @param ConnectionManagement|string $dbname Name of file without '_db' and extention '.xml'.
     */
    public function __construct($dbname)
    {
        // Create the object ConnectionManagement
        if (is_string($dbname)) {
            $this->_connectionManagement = new ConnectionManagement($dbname);
        } elseif ($dbname instanceof ConnectionManagement) {
            $this->_connectionManagement = $dbname;
        }

        // Create the proper driver
        if ($this->_connectionManagement->getDriver() == "sqlrelay") {
            $this->_dbDriver = new DBSQLRelayDriver($this->_connectionManagement);
        } elseif ($this->_connectionManagement->getDriver() == "oci8") {
            $this->_dbDriver = new DBOci8Driver($this->_connectionManagement);
        } else {
            $this->_dbDriver = DBPDODriver::factory($this->_connectionManagement);
        }
    }

    /**
     * @return ConnectionManagement
     */
    public function getConnectionManagement() 
    {
        return $this->_connectionManagement;
    }

    public function testConnection()
    {
        return true;
    }


    public function setCacheEngine(CacheEngineInterface $cache)
    {
        $this->_cacheEngine = $cache;
    }

    /**
     * @return CacheEngineInterface
     * @throws NotAvailableException
     */
    public function getCacheEngine()
    {
        if (is_null($this->_cacheEngine)) {
            throw new NotAvailableException('Cache Engine not available');
        }
        return $this->_cacheEngine;
    }

    /**
     * Get the DBDriver
     * @return DBDriverInterface
     */
    public function getDbDriver()
    {
        return $this->_dbDriver;
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
        $cache = $this->getCacheEngine()->get($key, $ttl);
        if ($cache === false) {
            $cache = array();
            $it = $this->getDbDriver()->getIterator($sql, $params);
            foreach ($it as $value) {
                $cache[] = $value->toArray();
            }

            $this->getCacheEngine()->set($key, $cache, $ttl);
        }

        $arrayDS = new ArrayDataset($cache);
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
     * @param IteratorInterface $it
     * @param string $fieldPK
     * @param string $fieldName
     * @return Resource
     */
    public function getArrayField(IteratorInterface $it, $fieldPK, $fieldName)
    {
        $result = array();
        while ($it->hasNext()) {
            $registro = $it->moveNext();
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
    protected $_dbFunction = null;

    /**
     * Get a IDbFunctions class to execute Database specific operations.
     *
*@return DbFunctionsInterface
     */
    public function getDbFunctions()
    {
        if (is_null($this->_dbFunction)) {
            $dbFunc = "\\ByJG\\AnyDataset\\Database\\Db" . ucfirst($this->_connectionManagement->getDriver()) . "Functions";
            $this->_dbFunction = new $dbFunc();
        }

        return $this->_dbFunction;
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
