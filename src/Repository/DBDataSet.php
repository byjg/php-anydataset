<?php

namespace ByJG\AnyDataset\Repository;

use PDO;
use ByJG\AnyDataset\Database\ConnectionManagement;
use ByJG\AnyDataset\Database\DBOci8Driver;
use ByJG\AnyDataset\Database\DBPDODriver;
use ByJG\AnyDataset\Database\DBSQLRelayDriver;
use ByJG\AnyDataset\Database\IDBDriver;
use ByJG\AnyDataset\Database\IDbFunctions;

class DBDataSet
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/**
	 *
	 * @var IDBDriver
	 */
	protected $_dbDriver = null;

	/**
	 *@param string $dbname - Name of file without '_db' and extention '.xml'.
	 *@desc Constructor
	 */
	public function __construct($dbname)
	{
		$this->_connectionManagement = new ConnectionManagement ( $dbname );

		if ($this->_connectionManagement->getDriver() == "sqlrelay") {
            $this->_dbDriver = new DBSQLRelayDriver($this->_connectionManagement);
        } elseif ($this->_connectionManagement->getDriver() == "oci8") {
            $this->_dbDriver = new DBOci8Driver($this->_connectionManagement);
        } else {
            $this->_dbDriver = DBPDODriver::factory($this->_connectionManagement);
        }
    }

	public function getDbType()
	{
		return $this->_connectionManagement->getDbType ();
	}

	public function getDbConnectionString()
	{
		return $this->_connectionManagement->getDbConnectionString ();
	}

	public function testConnection()
	{
		return true;
	}

	/**
	 * @access public
	 * @param string $sql
	 * @param array $array
	 * @return IIterator
	 */
	public function getIterator($sql, $array = null)
	{
		return $this->_dbDriver->getIterator($sql, $array);
	}

	public function getScalar($sql, $array = null)
	{
		return $this->_dbDriver->getScalar($sql, $array);
	}

	/**
	 *@access public
	 *@param string $tablename
	 *@return array
	 */
	public function getAllFields($tablename)
	{
		return $this->_dbDriver->getAllFields($tablename);
	}

	/**
	 *@access public
	 *@param string $sql
	 *@param array $array
	 *@return Resource
	 */
	public function execSQL($sql, $array = null)
	{
		$this->_dbDriver->executeSql($sql, $array);
	}

	public function beginTransaction()
	{
		$this->_dbDriver->beginTransaction();
	}

	public function commitTransaction()
	{
		$this->_dbDriver->commitTransaction();
	}

	public function rollbackTransaction()
	{
		$this->_dbDriver->rollbackTransaction();
	}

	/**
	 *@access public
	 *@param IIterator $it
	 *@param string $fieldPK
	 *@param string $fieldName
	 *@return Resource
	 */
	public function getArrayField(IIterator $it, $fieldPK, $fieldName)
	{
		$result = array ();
		while ( $it->hasNext () )
		{
			$registro = $it->MoveNext ();
			$result [$registro->getField ( $fieldPK )] = $registro->getField ( $fieldName );
		}
		return $result;
	}

	/**
	 *@access public
	 *@return PDO
	 */
	public function getDBConnection()
	{
		return $this->_dbDriver->getDbConnection();
	}

	/**
	 *
	 * @var IDbFunctions
	 */
	protected $_dbFunction = null;

	/**
	 * Get a IDbFunctions class to execute Database specific operations.
	 * @return IDbFunctions
	 */
	public function getDbFunctions()
	{
		if ($this->_dbFunction == null)
		{
			$dbFunc = "\\ByJG\\AnyDataset\\Database\\DB" . ucfirst($this->_connectionManagement->getDriver()) . "Functions";
			$this->_dbFunction = new $dbFunc();
		}

		return $this->_dbFunction;
	}

	public function setDriverAttribute($name, $value)
	{
		return $this->_dbDriver->setAttribute($name, $value);
	}

	public function getDriverAttribute($name)
	{
		return $this->_dbDriver->getAttribute($name);
	}

}
