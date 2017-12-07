<?php
/**
 * User: jg
 * Date: 28/11/16
 * Time: 22:54
 */

namespace Tests\AnyDataset\Store\Helpers;

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

    public function testDelimiterField()
    {
        $field = $this->object->delimiterField('field1');
        $field2 = $this->object->delimiterField('table.field1');
        $fieldAr = $this->object->delimiterField(['field2', 'field3']);
        $fieldAr2 = $this->object->delimiterField(['table.field2', 'table.field3']);

        $this->assertEquals('`field1`', $field);
        $this->assertEquals('`table`.`field1`', $field2);
        $this->assertEquals(['`field2`', '`field3`'], $fieldAr);
        $this->assertEquals(['`table`.`field2`', '`table`.`field3`'], $fieldAr2);
    }

    public function testDelimiterTable()
    {
        $table = $this->object->delimiterField('table');
        $tableDb = $this->object->delimiterField('db.table');

        $this->assertEquals('`table`', $table);
        $this->assertEquals('`db`.`table`', $tableDb);
    }

    // public function testToDate()
    // {
    // }
    //
    // public function testFromDate()
    // {
    // }

    public function testForUpdate()
    {
        $this->assertTrue($this->object->hasForUpdate());

        $sql1 = 'select * from table';
        $sql2 = 'select * from table for update';
        $sql3 = 'select * from table for update ';

        $this->assertEquals(
            'select * from table FOR UPDATE ',
            $this->object->forUpdate($sql1)
        );

        $this->assertEquals(
            'select * from table for update',
            $this->object->forUpdate($sql2)
        );

        $this->assertEquals(
            'select * from table for update ',
            $this->object->forUpdate($sql3)
        );
    }
}
