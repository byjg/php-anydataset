<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Store\MongoDbDriver;
use ByJG\AnyDataset\Store\NoSqlDocumentInterface;
use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\Util\Uri;
use InvalidArgumentException;

/**
 * Class NoSqlDataset
 * @todo Essa classe não tem sentido... (inclusive DBDataset).
 * @todo Por que não criar um factory que devolve um objeto que implemente a interface de acordo com o Schema?
 * @package ByJG\AnyDataset\Dataset
 */
class NoSqlDataset implements NoSqlDocumentInterface
{

    /**
     * Enter description here...
     *
     * @var Uri
     */
    private $connectionUri;

    /**
 * @var NoSqlDocumentInterface
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
     * @return MongoDbDriver|NoSqlDocumentInterface
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
