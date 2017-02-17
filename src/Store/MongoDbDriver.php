<?php

namespace ByJG\AnyDataset\Store;

use ByJG\AnyDataset\Dataset\IteratorFilter;
use ByJG\AnyDataset\Enum\Relation;
use ByJG\AnyDataset\NoSqlDocument;
use ByJG\AnyDataset\NoSqlDocumentInterface;
use ByJG\Serializer\BinderObject;
use ByJG\Util\Uri;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoDbDriver implements NoSqlDocumentInterface
{
    const MONGO_DOCUMENT = [
        Binary::class,
        Decimal128::class,
        Javascript::class,
        ObjectID::class,
        Timestamp::class,
        UTCDateTime::class,
    ];

    /**
     * @var MongoDB
     */
    protected $dataset = null;

    /**
     *
     * @var Manager;
     */
    protected $mongoManager = null;

    /**
     * Enter description here...
     *
     * @var Uri
     */
    protected $connectionUri;

    protected $database;

    /**
     * Creates a new MongoDB connection. This class is managed from NoSqlDataset
     *
     *  mongodb://username:passwortd@host:port/database
     *
     * @param Uri $connUri
     */
    public function __construct(Uri $connUri)
    {
        $this->connectionUri = $connUri;

        $hosts = $this->connectionUri->getHost();
        $port = $this->connectionUri->getPort() == '' ? 27017 : $this->connectionUri->getPort();
        $path = preg_replace('~^/~', '', $this->connectionUri->getPath());
        $database = $path;
        $username = $this->connectionUri->getUsername();
        $password = $this->connectionUri->getPassword();

        if ($username != '' && $password != '') {
            $auth = array('username' => $username, 'password' => $password, 'connect' => 'true');
        } else {
            $auth = array('connect' => 'true');
        }

        $connectString = sprintf('mongodb://%s:%d', $hosts, $port);
        $this->mongoManager = new Manager($connectString, $auth);
        $this->database = $database;
    }

    /**
     * Closes and destruct the MongoDB connection
     */
    public function __destruct()
    {
        // $this->mongoManager->
    }

    /**
     * Gets the instance of MongoDB; You do not need uses this directly.
     * If you have to, probably something is missing in this class
     * @return Manager
     */
    public function getDbConnection()
    {
        return $this->mongoManager;
    }

    /**
     * @param $idDocument
     * @param null $collection
     * @return \ByJG\AnyDataset\NoSqlDocument|null
     */
    public function getDocumentById($idDocument, $collection = null)
    {
        $filter = new IteratorFilter();
        $filter->addRelation('_id', Relation::EQUAL, $idDocument);
        $document = $this->getDocuments($filter, $collection);

        if (empty($document)) {
            return null;
        }

        return $document[0];
    }

    /**
     * @param \ByJG\AnyDataset\Dataset\IteratorFilter $filter
     * @param null $collection
     * @return \ByJG\AnyDataset\NoSqlDocument[]|null
     */
    public function getDocuments(IteratorFilter $filter, $collection = null)
    {
        if (empty($collection)) {
            throw new \InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $dataCursor = $this->mongoManager->executeQuery(
            $this->database . '.' . $collection,
            $this->getMongoFilterArray($filter)
        );

        if (empty($dataCursor)) {
            return null;
        }

        $data = $dataCursor->toArray();

        $result = [];
        foreach ($data as $item) {
            $result[] = new NoSqlDocument(
                $item->_id,
                $collection,
                BinderObject::toArrayFrom($item, false, self::MONGO_DOCUMENT)
            );
        }

        return $result;
    }

    protected function getMongoFilterArray(IteratorFilter $filter)
    {
        $result = [];

        foreach ($filter->getRawFilters() as $itemFilter) {
            $name = $itemFilter[1];
            $relation = $itemFilter[2];
            $value = $itemFilter[3];

            if ($itemFilter[0] == ' or ') {
                throw new \InvalidArgumentException('MongoDBDriver does not support the addRelationOr');
            }

            if (isset($result[$name])) {
                throw new \InvalidArgumentException('MongoDBDriver does not support filtering the same field twice');
            }

            switch ($relation) {
                case Relation::EQUAL:
                    $result[$name] = $value;
                    break;

                case Relation::GREATER_THAN:
                    $result[$name] = [ '$gt' => $value ];
                    break;

                case Relation::LESS_THAN:
                    $result[$name] = [ '$lt' => $value ];
                    break;

                case Relation::GREATER_OR_EQUAL_THAN:
                    $result[$name] = [ '$gte' => $value ];
                    break;

                case Relation::LESS_OR_EQUAL_THAN:
                    $result[$name] = [ '$lte' => $value ];
                    break;

                case Relation::NOT_EQUAL:
                    $result[$name] = [ '$ne' => $value ];
                    break;

                case Relation::STARTS_WITH:
                    $result[$name] = "/$value.*";
                    break;

                case Relation::CONTAINS:
                    $result[$name] = "/.*$value.*";
                    break;

            }
        }

        return new Query($result);
    }

    public function deleteDocumentById($idDocument, $collection = null)
    {
        $filter = new IteratorFilter();
        $filter->addRelation('_id', Relation::EQUAL, $idDocument);
        $this->deleteDocuments($filter, $collection);
    }


    public function deleteDocuments(IteratorFilter $filter, $collection = null)
    {
        if (empty($collection)) {
            throw new \InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulkWrite = new BulkWrite();

        $bulkWrite->delete($this->getMongoFilterArray($filter));
        $this->mongoManager->executeBulkWrite(
            $this->database . '.' . $collection,
            $bulkWrite,
            $writeConcern
        );
    }

    public function save(NoSqlDocument $document)
    {
        if (empty($document->getCollection())) {
            throw new \InvalidArgumentException('Collection is mandatory for MongoDB');
        }

        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulkWrite = new BulkWrite();

        $data = BinderObject::toArrayFrom($document->getDocument(), false, self::MONGO_DOCUMENT);

        if (empty($data['created'])) {
            $data['created'] = new UTCDateTime((new \DateTime())->getTimestamp()*1000);
        }

        $data['updated'] = new UTCDateTime((new \DateTime())->getTimestamp()*1000);

        if (empty($data['_id'])) {
            $data['_id']  = new ObjectID();
            $bulkWrite->insert($data);
        } else {
            $bulkWrite->update(['_id' => $data['_id']], ["\$set" => $data]);
        }

        $this->mongoManager->executeBulkWrite(
            $this->database . "." . $document->getCollection(),
            $bulkWrite,
            $writeConcern
        );

        $document->setDocument($data);
        $document->setIdDocument($data['_id']);

        return $document;
    }
}
