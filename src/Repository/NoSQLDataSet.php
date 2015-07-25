<?php

namespace ByJG\AnyDataset\Repository;

use InvalidArgumentException;
use ByJG\AnyDataset\Database\ConnectionManagement;
use ByJG\AnyDataset\Database\NoSQLDriverInterface;
use ByJG\AnyDataset\Database\MongoDBDriver;

class NoSQLDataSet implements NoSQLDriverInterface
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/**
	 *
	 * @var NoSQLDriverInterface
	 */
	protected $_dbDriver = null;

    /**
     *
     * @param type $dbname
     * @param type $collection
     * @throws InvalidArgumentException
     */
	public function __construct($dbname, $collection)
	{
		$this->_connectionManagement = new ConnectionManagement ( $dbname );

		if ($this->_connectionManagement->getDriver() == "mongodb")
			$this->_dbDriver = new MongoDBDriver($this->_connectionManagement, $collection);
		else
			throw new InvalidArgumentException("There is no '{$this->_connectionManagement->getDriver()}' NoSQL database");
	}

	public function getDbType()
	{
		return $this->_connectionManagement->getDbType();
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
	 *
	 * @return mixed
	 */
	public function getCollection()
	{
		return $this->_dbDriver->getCollection();
	}

	/**
	 *
	 * @param mixed $filter
	 * @return IteratorInterface $filter
	 */
	public function getIterator($filter = null, $fields = null)
	{
		return $this->_dbDriver->getIterator($filter, $fields);
	}

	/**
	 *
	 * @param void $document
	 */
	public function insert($document)
	{
		$this->_dbDriver->insert($document);
	}

	/**
	 *
	 * @param mixed $collection
	 * @return bool
	 */
	public function setCollection($collection)
	{
		return $this->_dbDriver->setCollection($collection);
	}

	/**
	 *
	 * @param mixed $document
	 * @param mixed $filter
	 * @return bool
	 */
	public function update($document, $filter = null, $options = null)
	{
		return $this->_dbDriver->update($document, $filter, $options);
	}


	/**
	 *@access public
	 *@return string
	 */
	public function getDBConnection()
	{
		return $this->_dbDriver->getDbConnection();
	}


	/*
	public function setDriverAttribute($name, $value)
	{
		return $this->_dbDriver->setAttribute($name, $value);
	}

	public function getDriverAttribute($name)
	{
		return $this->_dbDriver->getAttribute($name);
	}
	 *
	 */

}


