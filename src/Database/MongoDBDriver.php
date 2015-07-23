<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\ArrayIIterator;
use InvalidArgumentException;
use MongoClient;
use MongoCollection;
use MongoDate;
use MongoDB;
use stdClass;

class MongoDBDriver implements INoSQLDriver
{
	/**
	 * @var MongoDB
	 */
	protected $_db = null;

	/**
	 *
	 * @var MongoClient;
	 */
	protected $_client = null;

	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/**
	 *
	 * @var MongoCollection MongoDB collection
	 */
	protected $_collection;

	/**
	 *
	 * @var string
	 */
	protected $_collectionName;

	/**
	 * Creates a new MongoDB connection. This class is managed from NoSQLDataSet
	 *
	 * @param ConnectionManagement $connMngt
	 * @param string $collection
	 */
	public function __construct($connMngt, $collection)
	{
		$this->_connectionManagement = $connMngt;

		$hosts = $this->_connectionManagement->getServer();
		$port = $this->_connectionManagement->getPort() == '' ? 27017 : $this->_connectionManagement->getPort();
		$database = $this->_connectionManagement->getDatabase();
		$username = $this->_connectionManagement->getUsername();
		$password = $this->_connectionManagement->getPassword();

		if ($username != '' && $password != '') {
            $auth = array('username' => $username, 'password' => $password, 'connect' => 'true');
        } else {
            $auth = array('connect' => 'true');
        }

        $connecting_string =  sprintf('mongodb://%s:%d', $hosts, $port);
		$this->_client = new MongoClient($connecting_string, $auth);
		$this->_db = new MongoDB($this->_client, $database);

		$this->setCollection($collection);
	}

	/**
	 * Closes and destruct the MongoDB connection
	 */
	public function __destruct()
	{
		$this->_client->close();
		$this->_db = null;
	}


	/**
	 *
	 * @return string
	 */
	public function getCollection()
	{
		return $this->_collectionName;
	}

	/**
	 * Gets the instance of MongoDB; You do not need uses this directly. If you have to, probably something is missing in this class
	 * @return \MongoDB
	 */
	public function getDbConnection()
	{
		return $this->_db;
	}

	/**
	 * Return a IIterator
     *
	 * @param array $filter
	 * @param array $fields
	 * @return ArrayIIterator
	 */
	public function getIterator($filter = null, $fields = null)
	{
		if (is_null($filter))
		{
			$filter = array();
		}
		if (is_null($fields))
		{
			$fields = array();
		}
		$cursor = $this->_collection->find($filter, $fields);
		$arrIt = iterator_to_array($cursor);

		return new ArrayIIterator($arrIt);
	}

	/**
	 * Insert a document in the MongoDB
	 * @param mixed $document
	 * @return bool
	 */
	public function insert($document)
	{
		if (is_array($document))
		{
			$document['created_at'] = new MongoDate();
		}
		else if ($document instanceof stdClass)
		{
			$document->created_at = new MongoDate();
		}

		return $this->_collection->insert($document);
	}

	/**
	 * Defines the new Collection
	 * @param string $collection
	 */
	public function setCollection($collection)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$this->_collectionName = $collection;
	}

	/**
	 * Update a document based on your criteria
	 *
	 * Options for MongoDB is an array of:
	 *
	 * sort array	Determines which document the operation will modify if the
	 *              query selects multiple documents. findAndModify will modify
	 *              the first document in the sort order specified by this argument.
	 * remove boolean	Optional if update field exists. When TRUE, removes the
	 *              selected document. The default is FALSE.
	 * update array	Optional if remove field exists. Performs an update of the
	 *              selected document.
	 * new boolean	Optional. When TRUE, returns the modified document rather than the original.
	 *              The findAndModify method ignores the new option for remove operations.
	 *              The default is FALSE.
	 * upsert boolean	Optional. Used in conjunction with the update field. When TRUE,
	 *              the findAndModify command creates a new document if the query
	 *              returns no documents. The default is false. In MongoDB 2.2, the findAndModify
	 *              command returns NULL when upsert is TRUE.
	 *
	 * @param mixed $document
	 * @param array $filter
	 * @param array $options See:
	 * @return bool
	 */
	public function update($document, $filter = null, $options = null)
	{
		if (is_null($filter))
		{
			throw new InvalidArgumentException('You need to set the filter for update, or pass an empty array for all fields');
		}

		$update = array();
		if (is_array($document))
		{
			$document['updated_at'] = new MongoDate();
		}
		if ($document instanceof stdClass)
		{
			$document->updated_at = new MongoDate();
		}
		foreach ($document as $key => $value)
		{
			if ($key[0] == '$')
			{
				$update[$key] = $value;
			}
			else
			{
				$update['$set'][$key] = $value;
			}
		}

		if (is_null($options))
		{
			$options = array('new' => true);
		}
		return $this->_collection->findAndModify($filter, $update, array(), $options);
	}


	/*
	public function getAttribute($name)
	{
		$this->_db->getAttribute($name);
	}

	public function setAttribute($name, $value)
	{
		$this->_db->setAttribute ( $name, $value );
	}
	*/
}
