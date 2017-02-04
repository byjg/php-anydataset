<?php

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\JsonDataset;
use ByJG\AnyDataset\Repository\SingleRow;

/**
 * NOTE: The class name must end with "Test" suffix.
 */
class JsonDatasetTest extends PHPUnit_Framework_TestCase
{

    const JSON_OK = '[{"name":"Joao","surname":"Magalhaes","age":"38"},{"name":"John","surname":"Doe","age":"20"},{"name":"Jane","surname":"Smith","age":"18"}]';
    const JSON_NOTOK = '"name":"Joao","surname":"Magalhaes","age":"38"}]';
    const JSON_OK2 = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open"}, {"id": "OpenNew", "label": "Open New"} ]}}';

    protected $arrTest = array();
    protected $arrTest2 = array();

    // Run before each test case
    public function setUp()
    {
        $this->arrTest = array();
        $this->arrTest[] = array("name" => "Joao", "surname" => "Magalhaes", "age" => 38);
        $this->arrTest[] = array("name" => "John", "surname" => "Doe", "age" => 20);
        $this->arrTest[] = array("name" => "Jane", "surname" => "Smith", "age" => 18);

        $this->arrTest2 = array();
        $this->arrTest2[] = array("id" => "Open");
        $this->arrTest2[] = array("id" => "OpenNew", "label" => "Open New");
    }

    // Run end each test case
    public function teardown()
    {

    }

    public function test_createJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $this->assertTrue($jsonIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($jsonIterator->hasNext()); // "hasNext() method must be true");
        $this->assertEquals($jsonIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function test_navigateJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $count = 0;
        while ($jsonIterator->hasNext()) {
            $this->assertSingleRow($jsonIterator->moveNext(), $count++);
        }

        $this->assertEquals($jsonIterator->count(), 3); //, "Count() method must return 3");
    }

    public function test_navigateJsonIterator2()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $count = 0;
        foreach ($jsonIterator as $sr) {
            $this->assertSingleRow($sr, $count++);
        }

        $this->assertEquals($jsonIterator->count(), 3); //, "Count() method must return 3");
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\DatasetException
     */
    public function test_jsonNotWellFormatted()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_NOTOK);
    }

    public function navigateJSONComplex($path)
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonIterator = $jsonDataset->getIterator($path);

        $count = 0;
        foreach ($jsonIterator as $sr) {
            $this->assertSingleRow2($sr, $count++);
        }

        $this->assertEquals($jsonIterator->count(), 2); //, "Count() method must return 3");
    }

    public function test_navigateJSONComplexIterator()
    {
        $this->navigateJSONComplex("/menu/items");
    }

    public function test_navigateJSONComplexIteratorWithOutSlash()
    {
        $this->navigateJSONComplex("menu/items");
    }

    public function test_navigateJSONComplexIteratorWrongPath()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonIterator = $jsonDataset->getIterator("/menu/wrong");

        $this->assertEquals($jsonIterator->count(), 0); //, "Without throw error");
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\IteratorException
     */
    public function test_navigateJSONComplexIteratorWrongPath2()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonIterator = $jsonDataset->getIterator("/menu/wrong", true);
    }

    /**
     *
     * @param SingleRow $sr
     */
    public function assertSingleRow($sr, $count)
    {
        $this->assertEquals($sr->getField("name"), $this->arrTest[$count]["name"]);
        $this->assertEquals($sr->getField("surname"), $this->arrTest[$count]["surname"]);
        $this->assertEquals($sr->getField("age"), $this->arrTest[$count]["age"]);
    }

    public function assertSingleRow2($sr, $count)
    {
        $this->assertEquals($sr->getField("id"), $this->arrTest2[$count]["id"]);
        if ($count > 0) $this->assertEquals($sr->getField("label"), $this->arrTest2[$count]["label"]);
    }
}

?>