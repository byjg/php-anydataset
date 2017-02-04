<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Database\MongoDbDriver;
use ByJG\AnyDataset\Database\NoSqlDriverInterface;
use ByJG\AnyDataset\Exception\NotImplementedException;
use InvalidArgumentException;

class NoSqlDataset implements NoSqlDriverInterface
{

    /**
     * Enter description here...
     *
     * @var ConnectionManagement
     */
    private $_connectionManagement;

    /**

     * @var NoSqlDriverInterface
     */
    private $_dbDriver = null;

    /**
     *
     * @param string $dbname
     * @param string $collection
     * @throws InvalidArgumentException
     */
    public function __construct($dbname, $collection)
    {
        $this->_connectionManagement = new ConnectionManagement($dbname);

        if ($this->_connectionManagement->getDriver() == "mongodb") {
            $this->_dbDriver = new MongoDbDriver($this->_connectionManagement, $collection);
        } else {
            throw new InvalidArgumentException("There is no '{$this->_connectionManagement->getDriver()}' NoSQL database");
        }
    }

    /**
     * @return ConnectionManagement
     */
    public function getConnectionManagement()
    {
        return $this->_connectionManagement;
    }

    /**
     * @return MongoDbDriver|NoSqlDriverInterface
     */
    public function getDbDriver()
    {
        return $this->_dbDriver;
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
        return $this->getDbDriver()->getCollection();
    }

    /**
     *
     * @param mixed $filter
     * @param null $fields
     * @return IteratorInterface $filter
     */
    public function getIterator($filter = null, $fields = null)
    {
        return $this->getDbDriver()->getIterator($filter, $fields);
    }

    /**
     *
     * @param void $document
     */
    public function insert($document)
    {
        $this->getDbDriver()->insert($document);
    }

    /**
     *
     * @param mixed $collection
     * @return bool
     */
    public function setCollection($collection)
    {
        return $this->getDbDriver()->setCollection($collection);
    }

    /**
     *
     * @param mixed $document
     * @param mixed $filter
     * @param mixed $options
     * @return bool
     */
    public function update($document, $filter = null, $options = null)
    {
        return $this->getDbDriver()->update($document, $filter, $options);
    }

    /**
     * @access public
     * @return string
     */
    public function getDBConnection()
    {
        return $this->getDbDriver()->getDbConnection();
    }

    /**
     * @param $name
     * @param $value
     * @throws NotImplementedException
     */
    public function setDriverAttribute($name, $value)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * @param $name
     * @throws NotImplementedException
     */
    public function getDriverAttribute($name)
    {
        throw new NotImplementedException('Method not implemented');
    }
}
