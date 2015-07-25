<?php

namespace ByJG\AnyDataset\Repository;

use InvalidArgumentException;
use ByJG\Cache\ICacheEngine;

class CachedDBDataset extends DBDataSet
{

	/**
	 *
	 * @var ICacheEngine
	 */
	protected $_cacheEngine = null;

	/**
	 *
	 * @param string $dbname
	 * @param ICacheEngine $cacheEngine
	 * @throws InvalidArgumentException
	 */
	public function __construct($dbname, $cacheEngine)
	{
		if (!($cacheEngine instanceof ICacheEngine))
		{
			throw new InvalidArgumentException("I expected ICacheEngine object");
		}
		$this->_cacheEngine = $cacheEngine;
		parent::__construct($dbname);
	}

	public function getIterator($sql, $array = null, $ttl = 600)
	{
		$key1 = md5($sql);

		// Check which parameter exists in the SQL
		$arKey2 = array();
		if (is_array($array))
		{
			foreach($array as $key=>$value)
			{
				if (preg_match("/\[\[$key\]\]/", $sql))
				{
					$arKey2[$key] = $value;
				}
			}
		}

		// Define the query key
		if (is_array($arKey2) && count($arKey2) > 0)
			$key2 = ":" . md5(json_encode ($arKey2));
		else
			$key2 = "";
		$key = "qry:" . $key1 . $key2;

		// Get the CACHE
		$cache = $this->_cacheEngine->get($key, $ttl);
		if ($cache === false)
		{
			$cache = array();
			$it = parent::getIterator($sql, $array);
			foreach ($it as $value)
			{
				$cache[] = $value->toArray();
			}

			$this->_cacheEngine->set($key, $cache, $ttl);
		}

		$arrayDS = new ArrayDataSet($cache);
		return $arrayDS->getIterator();
	}

}

