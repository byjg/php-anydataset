<?php
/**
 * User: jg
 * Date: 28/11/16
 * Time: 22:54
 */

namespace Database;

use ByJG\AnyDataset\Store\Helpers\DbMysqlFunctions;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class DbMysqlFunctionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbMysqlFunctions
     */
    private $object;

    protected function setUp()
    {
        $this->object = new DbMysqlFunctions();
    }

    protected function tearDown()
    {
        $this->object = null;
    }

    public function testConcat()
    {
        $result = $this->object->concat('param1', 'param2');
        $this->assertEquals('concat(param1, param2)', $result);

        $result = $this->object->concat('param1', 'param2', 'param3');
        $this->assertEquals('concat(param1, param2, param3)', $result);

        $result = $this->object->concat('param1', 'param2', 'param3', 'param4');
        $this->assertEquals('concat(param1, param2, param3, param4)', $result);
    }

    public function testLimit()
    {
        $baseSql = 'select * from table';

        $result = $this->object->limit($baseSql, 10);
        $this->assertEquals($baseSql . ' LIMIT 10, 50', $result);

        $result = $this->object->limit($baseSql, 10, 20);
        $this->assertEquals($baseSql . ' LIMIT 10, 20', $result);

        $result = $this->object->limit($baseSql . ' LIMIT 5, 50', 10, 20);
        $this->assertEquals($baseSql . ' LIMIT 10, 20', $result);
    }

    public function testTop()
    {
        $baseSql = 'select * from table';

        $result = $this->object->top($baseSql, 10);
        $this->assertEquals($baseSql . ' LIMIT 0, 10', $result);

        $result = $this->object->top($baseSql . ' LIMIT 20,350', 10);
        $this->assertEquals($baseSql . ' LIMIT 0, 10', $result);
    }

    public function testHasTop()
    {
        $this->assertTrue($this->object->hasTop());
    }

    public function testHasLimit()
    {
        $this->assertTrue($this->object->hasLimit());
    }

    public function testSqlDate()
    {
        $this->assertEquals("DATE_FORMAT(column,'%d/%b/%Y')", $this->object->sqlDate('d/M/Y', 'column'));
        $this->assertEquals("DATE_FORMAT(column,'%d/%m/%Y %H:%i')", $this->object->sqlDate('d/m/Y H:i', 'column'));
        $this->assertEquals("DATE_FORMAT(column,'%H:%i')", $this->object->sqlDate('H:i', 'column'));
        $this->assertEquals("DATE_FORMAT(column,'%d %m %Y %H %i')", $this->object->sqlDate('d m Y H i', 'column'));
        $this->assertEquals("DATE_FORMAT(now(),'%d/%m/%y %I:%i')", $this->object->sqlDate('d/m/y h:i'));
        $this->assertEquals("DATE_FORMAT(column,'%b ')", $this->object->sqlDate('M q', 'column'));
    }

    // public function testToDate()
    // {
    // }
    //
    // public function testFromDate()
    // {
    // }
}
