<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Database\MongoDbDriver;
use ByJG\AnyDataset\Database\NoSqlDriverInterface;
use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\Util\Uri;
use InvalidArgumentException;

class NoSqlDataset implements NoSqlDriverInterface
{

    /**
     * Enter description here...
     *
     * @var Uri
     */
    private $connectionUri;

    /**

     * @var NoSqlDriverInterface
     */
    private $dbDriver = null;

    /**
     * @param string $connectionString
     * @param string $collection
     * @throws InvalidArgumentException
     */
    public function __construct($connectionString, $collection)
    {
        $this->connectionUri = new Uri($connectionString);

        if ($this->connectionUri->getScheme() == "mongodb") {
            $this->dbDriver = new MongoDbDriver($this->connectionUri, $collection);
        } else {
            throw new InvalidArgumentException("There is no '{$this->connectionUri->getScheme()}' NoSQL database");
        }
    }

    /**
     * @return Uri
     */
    public function getConnectionUri()
    {
        return $this->connectionUri;
    }

    /**
     * @return MongoDbDriver|NoSqlDriverInterface
     */
    public function getDbDriver()
    {
        return $this->dbDriver;
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
