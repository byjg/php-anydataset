<?php

namespace Store;

use ByJG\AnyDataset\Dataset\IteratorFilter;
use ByJG\AnyDataset\Enum\Relation;
use ByJG\AnyDataset\Factory;
use ByJG\AnyDataset\NoSqlDocument;
use ByJG\AnyDataset\Store\MongoDbDriver;

class MongoDbDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoDbDriver
     */
    protected $dbDriver;
    
    const TEST_COLLECTION = 'collectionTest';

    public function setUp()
    {
        $this->dbDriver = Factory::getNoSqlInstance('mongodb://192.168.1.181/test');

        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Hilux', 'brand' => 'Toyota', 'price' => 120000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'A3', 'brand' => 'Audi', 'price' => 90000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Fox', 'brand' => 'Volkswagen', 'price' => 40000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Corolla', 'brand' => 'Toyota', 'price' => 80000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Cobalt', 'brand' => 'Chevrolet', 'price' => 60000]
            )
        );
        $this->dbDriver->save(
            new NoSqlDocument(
                null,
                self::TEST_COLLECTION,
                ['name' => 'Uno', 'brand' => 'Fiat', 'price' =>35000]
            )
        );
    }
    
    public function tearDown()
    {
        $this->dbDriver->deleteDocuments(new IteratorFilter(), self::TEST_COLLECTION);
    }

    public function testSaveDocument()
    {
        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->addRelation('name', Relation::EQUAL, 'Hilux');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);

        // Check if get ONe
        $this->assertEquals(1, count($document));

        // Check if the default fields are here
        $data = $document[0]->getDocument();
        $this->assertNotEmpty($data['_id']);
        $this->assertNotEmpty($data['created']);
        $this->assertNotEmpty($data['updated']);
        unset($data['_id']);
        unset($data['created']);
        unset($data['updated']);

        // Check if the context is the expected
        $this->assertEquals(
            ['name' => 'Hilux', 'brand' => 'Toyota', 'price' => 120000],
            $data
        );

        // Create a new document with a partial field to update
        $documentToUpdate = new NoSqlDocument(
            $document[0]->getIdDocument(),
            self::TEST_COLLECTION,
            [ 'price' => 150000 ]
        );
        $documentSaved = $this->dbDriver->save($documentToUpdate);

        // Get the saved document
        $documentFromDb = $this->dbDriver->getDocumentById($document[0]->getIdDocument(), self::TEST_COLLECTION);

        // Check if the document have the same ID (Update) and Have the updated data
        $data = $documentFromDb->getDocument();
        $this->assertEquals($documentSaved->getIdDocument(), $document[0]->getIdDocument());
        $this->assertEquals($data['_id'], $document[0]->getIdDocument());
        $this->assertNotEmpty($data['created']);
        $this->assertNotEmpty($data['updated']);
        unset($data['_id']);
        unset($data['created']);
        unset($data['updated']);
        $this->assertEquals(
            ['name' => 'Hilux', 'brand' => 'Toyota', 'price' => 150000],
            $data
        );
    }

    public function testDelete()
    {
        // Get the Object to test
        $filter = new IteratorFilter();
        $filter->addRelation('name', Relation::EQUAL, 'Uno');
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(1, count($document));

        // Delete
        $this->dbDriver->deleteDocuments($filter, self::TEST_COLLECTION);

        // Check if object does not exists
        $document = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEmpty($document);
    }
}
