<?php
/**
 * User: jg
 * Date: 28/11/16
 * Time: 22:54
 */

namespace Tests\AnyDataset\Store\Helpers;

use ByJG\AnyDataset\Store\Helpers\DbPgsqlFunctions;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class DbPostgresFunctionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbPgsqlFunctions
     */
    private $object;

    protected function setUp()
    {
        $this->object = new DbPgsqlFunctions();
    }

    protected function tearDown()
    {
        $this->object = null;
    }

    public function testConcat()
    {
        $result = $this->object->concat('param1', 'param2');
        $this->assertEquals('param1 || param2', $result);

        $result = $this->object->concat('param1', 'param2', 'param3');
        $this->assertEquals('param1 || param2 || param3', $result);

        $result = $this->object->concat('param1', 'param2', 'param3', 'param4');
        $this->assertEquals('param1 || param2 || param3 || param4', $result);
    }

    public function testLimit()
    {
        $baseSql = 'select * from table';

        $result = $this->object->limit($baseSql, 10);
        $this->assertEquals($baseSql . ' LIMIT 50 OFFSET 10', $result);

        $result = $this->object->limit($baseSql, 10, 20);
        $this->assertEquals($baseSql . ' LIMIT 20 OFFSET 10', $result);

        $result = $this->object->limit($baseSql . ' LIMIT 5 OFFSET 50', 10, 20);
        $this->assertEquals($baseSql . ' LIMIT 20 OFFSET 10', $result);
    }

    public function testTop()
    {
        $baseSql = 'select * from table';

        $result = $this->object->top($baseSql, 10);
        $this->assertEquals($baseSql . ' LIMIT 10 OFFSET 0', $result);

        $result = $this->object->top($baseSql . ' LIMIT 350 OFFSET 20', 10);
        $this->assertEquals($baseSql . ' LIMIT 10 OFFSET 0', $result);
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
        $this->assertEquals("TO_CHAR(column,'DD/Mon/YYYY')", $this->object->sqlDate('d/M/Y', 'column'));
        $this->assertEquals("TO_CHAR(column,'DD/MM/YYYY HH24:MI')", $this->object->sqlDate('d/m/Y H:i', 'column'));
        $this->assertEquals("TO_CHAR(column,'HH24:MI')", $this->object->sqlDate('H:i', 'column'));
        $this->assertEquals("TO_CHAR(column,'DD MM YYYY HH24 MI')", $this->object->sqlDate('d m Y H i', 'column'));
        $this->assertEquals("TO_CHAR(column,'Mon Q')", $this->object->sqlDate('M q', 'column'));
        $this->assertEquals("TO_CHAR(current_timestamp,'DD/MM/YY HH:MI')", $this->object->sqlDate('d/m/y h:i'));
    }

    public function testDelimiterField()
    {
        $field = $this->object->delimiterField('field1');
        $field2 = $this->object->delimiterField('table.field1');
        $fieldAr = $this->object->delimiterField(['field2', 'field3']);
        $fieldAr2 = $this->object->delimiterField(['table.field2', 'table.field3']);

        $this->assertEquals('"field1"', $field);
        $this->assertEquals('"table"."field1"', $field2);
        $this->assertEquals(['"field2"', '"field3"'], $fieldAr);
        $this->assertEquals(['"table"."field2"', '"table"."field3"'], $fieldAr2);
    }

    public function testDelimiterTable()
    {
        $table = $this->object->delimiterField('table');
        $tableDb = $this->object->delimiterField('db.table');

        $this->assertEquals('"table"', $table);
        $this->assertEquals('"db"."table"', $tableDb);
    }

    // public function testToDate()
    // {
    // }
    //
    // public function testFromDate()
    // {
    // }
}
