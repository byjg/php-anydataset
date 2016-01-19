<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\Cache\CacheEngineInterface;
use InvalidArgumentException;

class CachedDBDataset
{

    /**
     *
     * @var CacheEngineInterface
     */
    protected $_cacheEngine = null;

    /**
     *
     * @var DBDataset
     */
    protected $_dbdataset = null;

    /**
     *
     * @param DBDataset $dbdataset
     * @param CacheEngineInterface $cacheEngine
     * @throws InvalidArgumentException
     */
    public function __construct(DBDataset $dbdataset, CacheEngineInterface $cacheEngine)
    {
        $this->_cacheEngine = $cacheEngine;
        $this->_dbdataset = $dbdataset;
    }

    public function getIterator($sql, $array = null, $ttl = 600)
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
        $key = "qry:" . $key1 . $key2;

        // Get the CACHE
        $cache = $this->_cacheEngine->get($key, $ttl);
        if ($cache === false) {
            $cache = array();
            $it = $this->_dbdataset->getIterator($sql, $array);
            foreach ($it as $value) {
                $cache[] = $value->toArray();
            }

            $this->_cacheEngine->set($key, $cache, $ttl);
        }

        $arrayDS = new ArrayDataset($cache);
        return $arrayDS->getIterator();
    }
}
