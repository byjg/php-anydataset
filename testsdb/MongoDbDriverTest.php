<?php

namespace TestsDb\AnyDataset;

use ByJG\AnyDataset\Dataset\IteratorFilter;
use ByJG\AnyDataset\Enum\Relation;
use ByJG\AnyDataset\Factory;
use ByJG\AnyDataset\NoSqlDocument;
use ByJG\AnyDataset\Store\MongoDbDriver;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class MongoDbDriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MongoDbDriver
     */
    protected $dbDriver;
    
    const TEST_COLLECTION = 'collectionTest';

    public function setUp()
    {
        $this->dbDriver = Factory::getNoSqlInstance('mongodb://mongodb-container/test');

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
        $this->assertEquals($data['created']->toDatetime(), $data['updated']->toDatetime());
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
        sleep(1); // Just to force a new Update DateTime
        $documentSaved = $this->dbDriver->save($documentToUpdate);

        // Get the saved document
        $documentFromDb = $this->dbDriver->getDocumentById($document[0]->getIdDocument(), self::TEST_COLLECTION);

        // Check if the document have the same ID (Update) and Have the updated data
        $data = $documentFromDb->getDocument();
        $this->assertEquals($documentSaved->getIdDocument(), $document[0]->getIdDocument());
        $this->assertEquals($data['_id'], $document[0]->getIdDocument());
        $this->assertNotEmpty($data['created']);
        $this->assertNotEmpty($data['updated']);
        $this->assertNotEquals($data['created']->toDatetime(), $data['updated']->toDatetime());
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

    public function testGetDocuments()
    {
        $filter = new IteratorFilter();
        $filter->addRelation('price', Relation::LESS_OR_EQUAL_THAN, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(2, count($documents));
        $this->assertEquals('Fox', $documents[0]->getDocument()['name']);
        $this->assertEquals('Volkswagen', $documents[0]->getDocument()['brand']);
        $this->assertEquals('40000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Uno', $documents[1]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[1]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->addRelation('price', Relation::LESS_THAN, 40000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(1, count($documents));
        $this->assertEquals('Uno', $documents[0]->getDocument()['name']);
        $this->assertEquals('Fiat', $documents[0]->getDocument()['brand']);
        $this->assertEquals('35000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->addRelation('price', Relation::GREATER_OR_EQUAL_THAN, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(2, count($documents));
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);
        $this->assertEquals('A3', $documents[1]->getDocument()['name']);
        $this->assertEquals('Audi', $documents[1]->getDocument()['brand']);
        $this->assertEquals('90000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->addRelation('price', Relation::GREATER_THAN, 90000);
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(1, count($documents));
        $this->assertEquals('Hilux', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('120000', $documents[0]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->addRelation('name', Relation::STARTS_WITH, 'Co');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(2, count($documents));
        $this->assertEquals('Corolla', $documents[0]->getDocument()['name']);
        $this->assertEquals('Toyota', $documents[0]->getDocument()['brand']);
        $this->assertEquals('80000', $documents[0]->getDocument()['price']);
        $this->assertEquals('Cobalt', $documents[1]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[1]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[1]->getDocument()['price']);

        $filter = new IteratorFilter();
        $filter->addRelation('name', Relation::CONTAINS, 'oba');
        $documents = $this->dbDriver->getDocuments($filter, self::TEST_COLLECTION);
        $this->assertEquals(1, count($documents));
        $this->assertEquals('Cobalt', $documents[0]->getDocument()['name']);
        $this->assertEquals('Chevrolet', $documents[0]->getDocument()['brand']);
        $this->assertEquals('60000', $documents[0]->getDocument()['price']);
    }
}
