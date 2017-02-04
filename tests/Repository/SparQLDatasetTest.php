<?php

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SparQLDataset;

/**
 * NOTE: The class name must end with "Test" suffix.
 */
class SparQLDatasetTest extends PHPUnit_Framework_TestCase
{

    const SPARQL_URL = 'http://dbpedia.org/sparql';

    protected static $SPARQL_NS = [
        'dbpedia-owl' => 'http://dbpedia.org/ontology/',
        'dbpprop' => 'http://dbpedia.org/property/'
    ];

    // Run before each test case
    public function setUp()
    {
        
    }

    // Run end each test case
    public function teardown()
    {
        
    }

    public function test_connectSparQLDataset()
    {
        $dataset = new SparQLDataset(SparQLDatasetTest::SPARQL_URL, SparQLDatasetTest::$SPARQL_NS);
        $iterator = $dataset->getIterator("select distinct ?Concept where {[] a ?Concept} LIMIT 5");

        $this->assertTrue($iterator instanceof IteratorInterface);
        $this->assertTrue($iterator->hasNext());
        $this->assertEquals($iterator->Count(), 5);
    }

    /**
     * @expectedException \SparQL\ConnectionException
     */
    public function test_wrongSparQLDataset()
    {
        $dataset = new SparQLDataset("http://invaliddomain/", SparQLDatasetTest::$SPARQL_NS);
        $iterator = $dataset->getIterator("select distinct ?Concept where {[] a ?Concept} LIMIT 5");

        $this->assertTrue($iterator instanceof IteratorInterface);
        $this->assertTrue($iterator->hasNext());
        $this->assertEquals($iterator->Count(), 0);
    }

    /**
     * @expectedException \SparQL\Exception
     */
    public function test_wrongSparQLDataset2()
    {
        $dataset = new SparQLDataset(SparQLDatasetTest::SPARQL_URL);
        $iterator = $dataset->getIterator("?Concept where {[] a ?Concept} LIMIT 5");
    }

    public function test_navigateSparQLDataset()
    {
        $dataset = new SparQLDataset(SparQLDatasetTest::SPARQL_URL, SparQLDatasetTest::$SPARQL_NS);
        $iterator = $dataset->getIterator(
            'SELECT  ?name ?meaning
                WHERE 
                {
                    ?s a  dbpedia-owl:Name;
                    dbpprop:name  ?name;
                    dbpprop:meaning  ?meaning 
                    . FILTER (str(?name) = "John")
                }'
        );

        $this->assertTrue($iterator->hasNext());
        $this->assertEquals($iterator->count(), 1);

        $sr = $iterator->moveNext();

        $this->assertEquals($sr->getField("name"), "John");
        $this->assertEquals($sr->getField("name.type"), "literal");
        $this->assertEquals($sr->getField("meaning"), "Graced by Yahweh , Yahweh is gracious");
        $this->assertEquals($sr->getField("meaning.type"), "literal");

        $this->assertFalse($iterator->hasNext());
    }
}
