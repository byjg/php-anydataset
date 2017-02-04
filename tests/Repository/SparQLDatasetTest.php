<?php

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SparQLDataset;

/**
 * NOTE: The class name must end with "Test" suffix.
 */
class SparQLDatasetTest extends PHPUnit_Framework_TestCase
{

    const SPARQL_URL = 'http://rdf.ecs.soton.ac.uk/sparql/';

    protected static $SPARQL_NS = array("foaf" => "http://xmlns.com/foaf/0.1/");

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
        $iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");

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
        $iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");

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
        $iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");
    }

    public function test_navigateSparQLDataset()
    {
        $dataset = new SparQLDataset(SparQLDatasetTest::SPARQL_URL, SparQLDatasetTest::$SPARQL_NS);
        $iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 2");

        $this->assertTrue($iterator->hasNext());
        $this->assertEquals($iterator->count(), 2);

        $sr = $iterator->moveNext();

        //$this->assertEquals($sr->getField("person"), "b4ee30d00000000");
        $this->assertEquals($sr->getField("person.type"), "bnode");
        //$this->assertEquals($sr->getField("name"), "zm");
        $this->assertEquals($sr->getField("name.type"), "literal");
        $this->assertEquals($sr->getField("name.datatype"), "http://www.w3.org/2001/XMLSchema#string");

        $this->assertTrue($iterator->hasNext());
        $sr = $iterator->moveNext();

        //$this->assertEquals($sr->getField("person"), "bf1120a00000002");
        $this->assertEquals($sr->getField("person.type"), "bnode");
        //$this->assertEquals($sr->getField("name"), "trp");
        $this->assertEquals($sr->getField("name.type"), "literal");
        $this->assertEquals($sr->getField("name.datatype"), "http://www.w3.org/2001/XMLSchema#string");

        $this->assertTrue(!$iterator->hasNext());
    }

    public function test_capabilities()
    {
        $dataset = new SparQLDataset(SparQLDatasetTest::SPARQL_URL);

        $caps = $dataset->getCapabilities();

        if (count($caps) == 0) {        // If does not installed the capability on PHP system, skip test;
            $this->assertTrue(true);
        } else {
            $this->assertTrue($caps["select"][0] == 1);
            $this->assertTrue(!$caps["constant_as"][0] == 1);
            $this->assertTrue(!$caps["math_as"][0] == 1);
            $this->assertTrue($caps["count"][0] == 1);
            $this->assertTrue(!$caps["sample"][0] == 1);
            $this->assertTrue(!$caps["load"][0] == 1);
        }
    }
}
