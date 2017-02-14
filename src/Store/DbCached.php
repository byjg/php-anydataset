<?php
/**
 * User: jg
 * Date: 13/02/17
 * Time: 16:29
 */

namespace ByJG\AnyDataset\Store;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Dataset\ArrayDataset;
use ByJG\Util\Uri;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface;

class DbCached implements DbDriverInterface
{
    /**
     * @var \ByJG\AnyDataset\DbDriverInterface|null
     */
    protected $dbDriver = null;

    /**
     * @var CacheItemPoolInterface;
     */
    protected $cacheEngine = null;

    protected $timeToCache = 30;

    /**
     * DbCached constructor.
     *
     * @param \ByJG\AnyDataset\DbDriverInterface|null $dbDriver
     * @param \Psr\Cache\CacheItemPoolInterface $cacheEngine
     * @param int $timeToCache
     */
    public function __construct(DbDriverInterface $dbDriver, CacheItemPoolInterface $cacheEngine, $timeToCache = 30)
    {
        $this->dbDriver = $dbDriver;
        $this->cacheEngine = $cacheEngine;
        $this->timeToCache = $timeToCache;
    }


    public function getIterator($sql, $params = null)
    {
        // Otherwise try to get from cache
        $key = $this->getQueryKey($sql, $params);

        // Get the CACHE
        $cacheItem = $this->cacheEngine->getItem($key);
        if (!$cacheItem->isHit()) {
            $iterator = $this->dbDriver->getIterator($sql, $params);

            $cacheItem->set($iterator->toArray());
            $cacheItem->expiresAfter(DateInterval::createFromDateString($this->timeToCache . " seconds"));

            $this->cacheEngine->save($cacheItem);
        }

        $arrayDS = new ArrayDataset($cacheItem->get());
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
        $this->dbDriver->getScalar($sql, $array);
    }

    public function getAllFields($tablename)
    {
        $this->dbDriver->getAllFields($tablename);
    }

    public function executeSql($sql, $array = null)
    {
        $this->dbDriver->executeSql($sql, $array);
    }

    public function beginTransaction()
    {
        $this->dbDriver->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->dbDriver->commitTransaction();
    }

    public function rollbackTransaction()
    {
        $this->dbDriver->rollbackTransaction();
    }

    public function getDbConnection()
    {
        $this->dbDriver->getDbConnection();
    }

    public function setAttribute($name, $value)
    {
        $this->dbDriver->setAttribute($name, $value);
    }

    public function getAttribute($name)
    {
        $this->dbDriver->getAttribute($name);
    }

    public function executeSqlAndGetId($sql, $array = null)
    {
        $this->dbDriver->executeSqlAndGetId($sql, $array);
    }

    public function getDbHelper()
    {
        $this->dbDriver->getDbHelper();
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->dbDriver->getUri();
    }
}