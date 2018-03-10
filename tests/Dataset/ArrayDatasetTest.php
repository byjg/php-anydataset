<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Dataset\ArrayDataset;
use PHPUnit\Framework\TestCase;
use Tests\AnyDataset\Sample\ModelGetter;
use ByJG\AnyDataset\IteratorInterface;
use Tests\AnyDataset\Sample\ModelPublic;

class ArrayDatasetTest extends TestCase
{

    protected $fieldNames;
    protected $SAMPLE1 = array("ProdA", "ProdB", "ProdC");
    protected $SAMPLE2 = array("A" => "ProdA", "B" => "ProdB", "C" => "ProdC");
    protected $SAMPLE3 = array("A" => array('code' => 1000, 'name' => "ProdA"),
        "B" => array('code' => 1001, 'name' => "ProdB"),
        "C" => array('code' => 1002, 'name' => "ProdC"));

    // Run before each test case
    public function setUp()
    {
        
    }

    // Run end each test case
    public function teardown()
    {
        
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidConstructor()
    {
        new ArrayDataset('aaa');
    }

    public function testcreateArrayIteratorSample1()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE1);
        $arrayIterator = $arrayDataset->getIterator();


        $this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
        $this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function testcreateArrayIteratorSample2()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE2);
        $arrayIterator = $arrayDataset->getIterator();

        $this->assertTrue($arrayIterator instanceof IteratorInterface); // "Resultant object must be an interator");
        $this->assertTrue($arrayIterator->hasNext()); // "hasNext() method must be true");
        $this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function testcreateArrayIteratorSample3()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE3);
        $arrayIterator = $arrayDataset->getIterator();

        $this->assertTrue($arrayIterator instanceof IteratorInterface); // "Resultant object must be an interator");
        $this->assertTrue($arrayIterator->hasNext()); // "hasNext() method must be true");
        $this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function testnavigateArrayIteratorSample1()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE1);
        $arrayIterator = $arrayDataset->getIterator();

        $count = 0;

        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 0);
            $this->assertField($sr, "__key", 0);
            $this->assertField($sr, "value", 'ProdA');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 1);
            $this->assertField($sr, "__key", 1);
            $this->assertField($sr, "value", 'ProdB');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 2);
            $this->assertField($sr, "__key", 2);
            $this->assertField($sr, "value", 'ProdC');
            $count++;
        }
        $this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
        $this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
    }

    public function testnavigateArrayIteratorSample2()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE2);
        $arrayIterator = $arrayDataset->getIterator();

        $count = 0;

        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 0);
            $this->assertField($sr, "__key", 'A');
            $this->assertField($sr, "value", 'ProdA');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 1);
            $this->assertField($sr, "__key", 'B');
            $this->assertField($sr, "value", 'ProdB');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 2);
            $this->assertField($sr, "__key", 'C');
            $this->assertField($sr, "value", 'ProdC');
            $count++;
        }
        $this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
        $this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
    }

    public function testnavigateArrayIteratorSample3()
    {
        $arrayDataset = new ArrayDataset($this->SAMPLE3);
        $arrayIterator = $arrayDataset->getIterator();

        $count = 0;

        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 0);
            $this->assertField($sr, "__key", 'A');
            $this->assertField($sr, "code", 1000);
            $this->assertField($sr, "name", 'ProdA');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 1);
            $this->assertField($sr, "__key", 'B');
            $this->assertField($sr, "code", 1001);
            $this->assertField($sr, "name", 'ProdB');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 2);
            $this->assertField($sr, "__key", 'C');
            $this->assertField($sr, "code", 1002);
            $this->assertField($sr, "name", 'ProdC');
            $count++;
        }
        $this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
        $this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
    }

    public function testcreateFromModel1()
    {
        $model = array(
            new ModelPublic(1, 'ProdA'),
            new ModelPublic(2, 'ProdB'),
            new ModelPublic(3, 'ProdC')
        );

        $arrayDataset = new ArrayDataset($model);
        $arrayIterator = $arrayDataset->getIterator();


        $this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
        $this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function testnavigateFromModel1()
    {
        $model = array(
            new ModelPublic(1, 'ProdA'),
            new ModelPublic(2, 'ProdB'),
            new ModelPublic(3, 'ProdC')
        );

        $arrayDataset = new ArrayDataset($model);
        $arrayIterator = $arrayDataset->getIterator();

        $count = 0;

        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 0);
            $this->assertField($sr, "__key", 0);
            $this->assertField($sr, "__class", "Tests\\AnyDataset\\Sample\\ModelPublic");
            $this->assertField($sr, "id", 1);
            $this->assertField($sr, "name", 'ProdA');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 1);
            $this->assertField($sr, "__key", 1);
            $this->assertField($sr, "__class", "Tests\\AnyDataset\\Sample\\ModelPublic");
            $this->assertField($sr, "id", 2);
            $this->assertField($sr, "name", 'ProdB');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 2);
            $this->assertField($sr, "__key", 2);
            $this->assertField($sr, "__class", "Tests\\AnyDataset\\Sample\\ModelPublic");
            $this->assertField($sr, "id", 3);
            $this->assertField($sr, "name", 'ProdC');
            $count++;
        }
        $this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
        $this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
    }

    public function testcreateFromModel2()
    {
        $model = array(
            new ModelGetter(1, 'ProdA'),
            new ModelGetter(2, 'ProdB'),
            new ModelGetter(3, 'ProdC')
        );

        $arrayDataset = new ArrayDataset($model);
        $arrayIterator = $arrayDataset->getIterator();


        $this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
        $this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
    }

    public function testnavigateFromModel2()
    {
        $model = array(
            new ModelGetter(1, 'ProdA'),
            new ModelGetter(2, 'ProdB'),
            new ModelGetter(3, 'ProdC')
        );

        $arrayDataset = new ArrayDataset($model);
        $arrayIterator = $arrayDataset->getIterator();

        $count = 0;

        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 0);
            $this->assertField($sr, "__key", 0);
            $this->assertField($sr, "__class", ModelGetter::class);
            $this->assertField($sr, "id", 1);
            $this->assertField($sr, "name", 'ProdA');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 1);
            $this->assertField($sr, "__key", 1);
            $this->assertField($sr, "__class", ModelGetter::class);
            $this->assertField($sr, "id", 2);
            $this->assertField($sr, "name", 'ProdB');
            $count++;
        }
        if ($arrayIterator->hasNext()) {
            $sr = $arrayIterator->moveNext();
            $this->assertField($sr, "__id", 2);
            $this->assertField($sr, "__key", 2);
            $this->assertField($sr, "__class", ModelGetter::class);
            $this->assertField($sr, "id", 3);
            $this->assertField($sr, "name", 'ProdC');
            $count++;
        }
        $this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
        $this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
    }

    /**
     * @param Row $row
     * @param $field
     * @param $value
     */
    public function assertField($row, $field, $value)
    {
        $this->assertEquals($value, $row->get($field));
    }
}

