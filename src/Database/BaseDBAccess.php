<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\AnyDatasetContext;
use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\AnyDataset\LogHandler;
use ByJG\AnyDataset\Repository\CachedDBDataset;
use ByJG\AnyDataset\Repository\DBDataSet;
use ByJG\AnyDataset\Repository\IIterator;
use ByJG\Cache\ICacheEngine;

abstract class BaseDBAccess
{

	/**
	 * @var DBDataSet
	 */
	protected $_db = null;

	protected $_cachedDb = null;

	/**
	 * Wrapper for SQLHelper
	 *
	 * @var SQLHelper
	 */
	protected $_sqlhelper = null;

	/**
	 * Base Class Constructor. Don't must be override.
	 *
	 */
	public function __construct()
	{
        // Nothing Here
	}

	/**
	 * This method must be overrided and the return must be a valid DBDataSet name.
	 *
	 * @return string
	 */
	public abstract function getDataBaseName();

	/**
	 * @return ICacheEngine
	 */
	public function getCacheEngine()
	{
		throw new NotImplementedException('You have to implement the cache engine in order to use the Cache');
	}

	/**
	 * Create a instance of DBDataSet to connect database
	 * @return DBDataSet
	 */
	protected function getDBDataSet($cache = false)
	{
		if (!$cache)
		{
			if (is_null($this->_db))
			{
				$this->_db = new DBDataSet($this->getDataBaseName());
			}

			return $this->_db;
		}
		else
		{
			if (is_null($this->_cachedDb))
			{
				$this->_cachedDb = new CachedDBDataset($this->getDataBaseName(), $this->getCacheEngine());
			}

			return $this->_cachedDb;
		}
	}

	/**
	 * Execute a SQL and dont wait for a response.
	 * @param string $sql
	 * @param string $param
	 * @param bool getId
	 */
	protected function executeSQL($sql, $param = null, $getId = false)
	{
		$dbfunction = $this->getDbFunctions();

		$debug = AnyDatasetContext::getInstance()->getDebug();
		$start = 0;
		if ($debug)
		{
			$log = LogHandler::getInstance();
			$log->debug("Class name: " . get_class($this));
			$log->debug("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach ($param as $key => $value)
				{
					if ($s != "")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				$log->debug("Params: $s");
			}
			$start = microtime(true);
		}

		if ($getId)
		{
			$id = $dbfunction->executeAndGetInsertedId($this->getDBDataSet(), $sql, $param);
		}
		else
		{
			$id = null;
			$this->getDBDataSet()->execSQL($sql, $param);
		}

		if ($debug)
		{
			$end = microtime(true);
			$log->debug("Execution time: " . ($end - $start) . " seconds ");
		}

		return $id;
	}

	/**
	 * Execulte SELECT SQL Query
	 *
	 * @param string $sql
	 * @param array $param
	 * @return IIterator
	 */
	protected function getIterator($sql, $param = null, $ttl = -1)
	{
		$db = $this->getDBDataSet($ttl > 0);

		$debug = AnyDatasetContext::getInstance()->getDebug();
		$start = 0;
		if ($debug)
		{
			$log = LogHandler::getInstance();
			$log->debug("Class name: " . get_class($this));
			$log->debug("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach ($param as $key => $value)
				{
					if ($s != "")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				$log->debug("Params: $s");
			}
			$start = microtime(true);
		}
		$it	= $db->getIterator($sql, $param, $ttl);
		if ($debug)
		{
			$end = microtime(true);
			$log->debug("Execution Time: " . ($end - $start) . " segundos ");
		}
		return $it;
	}

	protected function getScalar($sql, $param = null)
	{
		$this->getDBDataSet();

		$debug = AnyDatasetContext::getInstance()->getDebug();
		$start = 0;
		if ($debug)
		{
			$log = LogHandler::getInstance();
			$log->debug("Class name: " . get_class($this));
			$log->debug("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach ($param as $key => $value)
				{
					if ($s != "")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				$log->debug("Params: $s");
			}
			$start = microtime(true);
		}
		$scalar = $this->_db->getScalar($sql, $param);
		if ($debug)
		{
			$end = microtime(true);
			$log->debug("Execution Time: " . ($end - $start) . " segundos ");
		}
		return $scalar;
	}

	/**
	 * Get a SQLHelper object
	 *
	 * @return SQLHelper
	 */
	public function getSQLHelper()
	{
		$this->getDBDataSet();

		if (is_null($this->_sqlhelper))
		{
			$this->_sqlhelper = new SQLHelper($this->_db);
		}

		return $this->_sqlhelper;
	}

	/**
	 * Get an Interator from an ID. Ideal for get data from PK
	 *
	 * @param string $tablename
	 * @param string $key
	 * @param string $value
	 * @return IIterator
	 */
	protected function getIteratorbyId($tablename, $key, $value)
	{
		$sql   = "select * from $tablename where $key = [[$key]] ";
		$param = array();
		$param[$key] = $value;
		return $this->getIterator($sql, $param);
	}

	/**
	 * Get an Array from an existing Iterator
	 *
	 * @param IIterator $it
	 * @param string $key
	 * @param string $value
	 * @return array()
	 */
	public static function getArrayFromIterator(IIterator $it, $key, $value, $firstElement = "-- Selecione --")
	{
		$retArray = array();
		if ($firstElement != "")
		{
			$retArray[""] = $firstElement;
		}
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$retArray[$sr->getField(strtolower($key))] = $sr->getField(strtolower($value));
		}
		return $retArray;
	}

	/**
	 *
	 * @param IIterator $it
	 * @param string $name
	 * @param array fields
	 * @param bool $echoToBrowser
	 */
	public static function saveToCSV($it, $name = "data.csv", $fields = null, $echoToBrowser = true)
	{
		if ($echoToBrowser)
		{
			ob_clean();

			header("Content-Type: text/csv;");
			header("Content-Disposition: attachment; filename=$name");
		}

		$first = true;
		$line  = "";
		foreach ($it as $sr)
		{
			if ($first)
			{
				$first = false;

				if ($fields == null)
				{
					$fields = $sr->getFieldNames();
				}

				$line .= '"' . implode('","', $fields) . '"' . "\n";
			}

			$raw = array();
			foreach ($fields as $field)
			{
				$raw[] = $sr->getField($field);
			}
			$line .= '"' . implode('","', array_values($raw)) . '"' . "\n";

			if ($echoToBrowser)
			{
				echo $line;
				$line = "";
			}
		}

		if (!$echoToBrowser)
		{
			return $line;
		}
	}

	/**
	 * Get a IDbFunctions class containing specific database operations
	 * @return IDBFunctions
	 */
	public function getDbFunctions()
	{
		return $this->getDBDataSet()->getDbFunctions();
	}

	public function beginTransaction()
	{
		$this->getDBDataSet()->beginTransaction();
	}

	public function commitTransaction()
	{
		$this->getDBDataSet()->commitTransaction();
	}

	public function rollbackTransaction()
	{
		$this->getDBDataSet()->rollbackTransaction();
	}

	public function getObjectDbDataSet()
	{
		return $this->_db;
	}

	public function joinTransactionContext(BaseDBAccess $dal)
	{
		if (is_null($dal->getObjectDbDataSet()))
		{
			throw new \Exception('Transaction not initialized');
		}
		$this->_db = $dal->getObjectDbDataSet();
	}

}

?>
