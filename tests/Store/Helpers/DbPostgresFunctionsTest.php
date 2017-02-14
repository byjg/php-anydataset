<?php
/**
 * User: jg
 * Date: 28/11/16
 * Time: 22:54
 */

namespace Database;

use ByJG\AnyDataset\Store\Helpers\DbPgsqlFunctions;

class DbPostgresFunctionsTest extends \PHPUnit_Framework_TestCase
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

    public function testToDate()
    {
    }

    public function testFromDate()
    {

    }
}
