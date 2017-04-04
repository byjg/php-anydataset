<?php
/**
 * User: jg
 * Date: 28/11/16
 * Time: 22:54
 */

namespace Database;

use ByJG\AnyDataset\Store\Helpers\DbDblibFunctions;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class DbDblibFunctionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbDblibFunctions
     */
    private $object;

    protected function setUp()
    {
        $this->object = new DbDblibFunctions();
    }

    protected function tearDown()
    {
        $this->object = null;
    }

    public function testConcat()
    {
        $result = $this->object->concat('param1', 'param2');
        $this->assertEquals('param1 + param2', $result);

        $result = $this->object->concat('param1', 'param2', 'param3');
        $this->assertEquals('param1 + param2 + param3', $result);

        $result = $this->object->concat('param1', 'param2', 'param3', 'param4');
        $this->assertEquals('param1 + param2 + param3 + param4', $result);
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\NotAvailableException
     */
    public function testLimit()
    {
        $this->object->limit('select  from table', 0, 10);
    }

    public function testTop()
    {
        $result = $this->object->top('select * from table', 10);
        $this->assertEquals('select top 10 * from table', $result);

        $result = $this->object->top('select TOP 234 * from table', 20);
        $this->assertEquals('select TOP 20 * from table', $result);
    }

    public function testHasTop()
    {
        $this->assertTrue($this->object->hasTop());
    }

    public function testHasLimit()
    {
        $this->assertFalse($this->object->hasLimit());
    }

    public function testSqlDate()
    {
        $this->assertEquals("FORMAT(column, 'dd/MM/YYYY')", $this->object->sqlDate('d/M/Y', 'column'));
        $this->assertEquals("FORMAT(column, 'dd/M/YYYY HH:mm')", $this->object->sqlDate('d/m/Y H:i', 'column'));
        $this->assertEquals("FORMAT(column, 'HH:mm')", $this->object->sqlDate('H:i', 'column'));
        $this->assertEquals("FORMAT(column, 'dd M YYYY HH mm')", $this->object->sqlDate('d m Y H i', 'column'));
        $this->assertEquals("FORMAT(getdate(), 'dd/M/YY H:mm')", $this->object->sqlDate('d/m/y h:i'));
        $this->assertEquals("FORMAT(column, 'MM ')", $this->object->sqlDate('M q', 'column'));
    }

    // public function testToDate()
    // {
    // }
    //
    // public function testFromDate()
    // {
    // }
}
