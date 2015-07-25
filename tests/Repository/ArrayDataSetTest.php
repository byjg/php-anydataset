<?php

use ByJG\AnyDataset\Repository\ArrayDataSet;
use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class ArrayDataSetTest extends PHPUnit_Framework_TestCase
{
	protected $fieldNames;

	protected $SAMPLE1 = array("ProdA", "ProdB", "ProdC");
	protected $SAMPLE2 = array("A"=>"ProdA", "B"=>"ProdB", "C"=>"ProdC");
	protected $SAMPLE3 = array("A"=>array('code'=>1000, 'name'=>"ProdA"),
		"B"=>array('code'=>1001, 'name'=>"ProdB"),
		"C"=>array('code'=>1002, 'name'=>"ProdC"));
	
	// Run before each test case
	function setUp()
	{

	}

	// Run end each test case
	function teardown()
	{
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	function test_InvalidConstructor()
	{
		$arrayDataset = new ArrayDataSet('aaa');
	}

	function test_createArrayIteratorSample1()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE1);
		$arrayIterator = $arrayDataset->getIterator();


		$this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
		$this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
		$this->assertEquals($arrayIterator->Count(), 3) ; //, "Count() method must return 3");
	}

	function test_createArrayIteratorSample2()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE2);
		$arrayIterator = $arrayDataset->getIterator();

		$this->assertTrue($arrayIterator instanceof IteratorInterface); // "Resultant object must be an interator");
		$this->assertTrue($arrayIterator->hasNext()); // "hasNext() method must be true");
		$this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
	}

	function test_createArrayIteratorSample3()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE3);
		$arrayIterator = $arrayDataset->getIterator();

		$this->assertTrue($arrayIterator instanceof IteratorInterface); // "Resultant object must be an interator");
		$this->assertTrue($arrayIterator->hasNext()); // "hasNext() method must be true");
		$this->assertEquals($arrayIterator->Count(), 3); //, "Count() method must return 3");
	}


	function test_navigateArrayIteratorSample1()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE1);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 0);
			$this->assertField($sr, $count, "__key", 0);
			$this->assertField($sr, $count, "value", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 1);
			$this->assertField($sr, $count, "__key", 1);
			$this->assertField($sr, $count, "value", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 2);
			$this->assertField($sr, $count, "__key", 2);
			$this->assertField($sr, $count, "value", 'ProdC');
			$count++;
		}
		$this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
		$this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
	}

	function test_navigateArrayIteratorSample2()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE2);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 0);
			$this->assertField($sr, $count, "__key", 'A');
			$this->assertField($sr, $count, "value", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 1);
			$this->assertField($sr, $count, "__key", 'B');
			$this->assertField($sr, $count, "value", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 2);
			$this->assertField($sr, $count, "__key", 'C');
			$this->assertField($sr, $count, "value", 'ProdC');
			$count++;
		}
		$this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
		$this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
	}

	function test_navigateArrayIteratorSample3()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE3);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 0);
			$this->assertField($sr, $count, "__key", 'A');
			$this->assertField($sr, $count, "code", 1000);
			$this->assertField($sr, $count, "name", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 1);
			$this->assertField($sr, $count, "__key", 'B');
			$this->assertField($sr, $count, "code", 1001);
			$this->assertField($sr, $count, "name", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 2);
			$this->assertField($sr, $count, "__key", 'C');
			$this->assertField($sr, $count, "code", 1002);
			$this->assertField($sr, $count, "name", 'ProdC');
			$count++;
		}
		$this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
		$this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
	}

	function test_createFromModel1()
	{
		$model = array(
			new Tests\Sample\ModelPublic(1, 'ProdA'),
			new Tests\Sample\ModelPublic(2, 'ProdB'),
			new Tests\Sample\ModelPublic(3, 'ProdC')
		);

		$arrayDataset = new ArrayDataSet($model);
		$arrayIterator = $arrayDataset->getIterator();


		$this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
		$this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
		$this->assertEquals($arrayIterator->Count(), 3) ; //, "Count() method must return 3");
	}

	function test_navigateFromModel1()
	{
		$model = array(
			new Tests\Sample\ModelPublic(1, 'ProdA'),
			new Tests\Sample\ModelPublic(2, 'ProdB'),
			new Tests\Sample\ModelPublic(3, 'ProdC')
		);

		$arrayDataset = new ArrayDataSet($model);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 0);
			$this->assertField($sr, $count, "__key", 0);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelPublic");
			$this->assertField($sr, $count, "id", 1);
			$this->assertField($sr, $count, "name", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 1);
			$this->assertField($sr, $count, "__key", 1);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelPublic");
			$this->assertField($sr, $count, "id", 2);
			$this->assertField($sr, $count, "name", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 2);
			$this->assertField($sr, $count, "__key", 2);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelPublic");
			$this->assertField($sr, $count, "id", 3);
			$this->assertField($sr, $count, "name", 'ProdC');
			$count++;
		}
		$this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
		$this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
	}

	function test_createFromModel2()
	{
		$model = array(
			new Tests\Sample\ModelGetter(1, 'ProdA'),
			new Tests\Sample\ModelGetter(2, 'ProdB'),
			new Tests\Sample\ModelGetter(3, 'ProdC')
		);

		$arrayDataset = new ArrayDataSet($model);
		$arrayIterator = $arrayDataset->getIterator();


		$this->assertTrue($arrayIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
		$this->assertTrue($arrayIterator->hasNext()); //, "hasNext() method must be true");
		$this->assertEquals($arrayIterator->Count(), 3) ; //, "Count() method must return 3");
	}

	function test_navigateFromModel2()
	{
		$model = array(
			new Tests\Sample\ModelGetter(1, 'ProdA'),
			new Tests\Sample\ModelGetter(2, 'ProdB'),
			new Tests\Sample\ModelGetter(3, 'ProdC')
		);

		$arrayDataset = new ArrayDataSet($model);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 0);
			$this->assertField($sr, $count, "__key", 0);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelGetter");
			$this->assertField($sr, $count, "id", 1);
			$this->assertField($sr, $count, "name", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 1);
			$this->assertField($sr, $count, "__key", 1);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelGetter");
			$this->assertField($sr, $count, "id", 2);
			$this->assertField($sr, $count, "name", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "__id", 2);
			$this->assertField($sr, $count, "__key", 2);
			$this->assertField($sr, $count, "__class", "Tests\Sample\ModelGetter");
			$this->assertField($sr, $count, "id", 3);
			$this->assertField($sr, $count, "name", 'ProdC');
			$count++;
		}
		$this->assertTrue(!$arrayIterator->hasNext()); //, 'I did not expected more records');
		$this->assertEquals($count, 3); //, "Count records mismatch. Need to process 3 records.");
	}
	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertField($sr, $line, $field, $value)
	{
		$this->assertEquals($sr->getField($field), $value); //, "At line $line field '$field' I expected '" . $value . "' but I got '" . $sr->getField($field) . "'");
	}

	function assertSingleRow2($sr, $count)
	{
		$this->assertEquals($sr->getField("__id"), $this->arrTest2[$count]["__id"]); //, "At line $count field 'id' I expected '" . $this->arrTest2[$count]["__id"] . "' but I got '" . $sr->getField("__id") . "'");
		if ($count > 0)
			$this->assertEquals($sr->getField("label"), $this->arrTest2[$count]["label"]); //, "At line $count field 'label' I expected '" . $this->arrTest2[$count]["label"] . "' but I got '" . $sr->getField("label") . "'");
	}

}
?>