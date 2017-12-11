<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Dataset\IteratorFilter;
use ByJG\AnyDataset\Dataset\IteratorFilterSqlFormatter;
use ByJG\AnyDataset\Dataset\IteratorFilterXPathFormatter;
use ByJG\AnyDataset\Dataset\Row;
use ByJG\AnyDataset\Enum\Relation;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class IteratorFilterTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var IteratorFilter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new IteratorFilter();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testGetXPath()
    {
        $this->assertEquals(
            '/anydataset/row',
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->addRelation('field', Relation::EQUAL, 'test');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->addRelation('field2', Relation::GREATER_OR_EQUAL_THAN, 'test2');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  and field[@name='field2'] >= 'test2' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->addRelation('field3', Relation::CONTAINS, 'test3');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  and field[@name='field2'] >= 'test2'  and  contains(field[@name='field3'] ,  'test3' ) ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );
    }

    public function testGetSql()
    {
        $params = null;
        $returnFields = '*';
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals([], $params);
        $this->assertEquals('select * from tablename ', $sql);

        $this->object->addRelation('field', Relation::EQUAL, 'test');
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field' => 'test'], $params);
        $this->assertEquals('select * from tablename  where  field = [[field]]  ', $sql);

        $this->object->addRelation('field2', Relation::GREATER_OR_EQUAL_THAN, 'test2');
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field' => 'test', 'field2' => 'test2'], $params);
        $this->assertEquals('select * from tablename  where  field = [[field]]  and  field2 >= [[field2]]  ', $sql);

        $this->object->addRelation('field3', Relation::CONTAINS, 'test3');
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field' => 'test', 'field2' => 'test2', 'field3' => '%test3%'], $params);
        $this->assertEquals('select * from tablename  where  field = [[field]]  and  field2 >= [[field2]]  and  field3  like  [[field3]]  ', $sql);
    }

    public function testSqlLiteral()
    {
        $literalObject = new class() {
            public function __toString()
            {
                return 'cast(\'10\' as integer)';
            }
        };

        $params = null;
        $returnFields = '*';
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals([], $params);
        $this->assertEquals('select * from tablename ', $sql);

        $this->object->addRelation('field', Relation::GREATER_THAN, $literalObject);
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals([], $params);
        $this->assertEquals('select * from tablename  where  field > cast(\'10\' as integer)  ', $sql);

        $this->object->addRelation('field2', Relation::LESS_THAN, 5);
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field2' => 5], $params);
        $this->assertEquals('select * from tablename  where  field > cast(\'10\' as integer)  and  field2 < [[field2]]  ', $sql);
    }

    public function testMatch()
    {

        $collection = [
            $row1 = new Row(
                [
                    'field' => 'value1',
                    'field2' => 'value2'
                ]
            ),
            $row2 = new Row(
                [
                    'field' => 'other1',
                    'field2' => 'other2'
                ]
            ),
            $row3 = new Row(
                [
                    'field' => 'last1',
                    'field2' => 'last2'
                ]
            )
        ];

        $this->assertEquals($collection, $this->object->match($collection));

        $this->object->addRelation('field2', Relation::EQUAL, 'other2');
        $this->assertEquals([ $row2], $this->object->match($collection));

        $this->object->addRelationOr('field', Relation::EQUAL, 'last1');
        $this->assertEquals([ $row2, $row3], $this->object->match($collection));


        //------------------------

        $this->object = new IteratorFilter();
        $this->object->addRelation('field', Relation::EQUAL, 'last1');
        $this->object->addRelation('field2', Relation::EQUAL, 'last2');
        $this->assertEquals([ $row3], $this->object->match($collection));
    }

    public function testAddRelationOr()
    {
        $this->object->addRelation('field', Relation::EQUAL, 'test');
        $this->object->addRelationOr('field2', Relation::EQUAL, 'test2');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  or field[@name='field2'] = 'test2' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $params = null;
        $returnFields = '*';
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field' => 'test', 'field2' => 'test2'], $params);
        $this->assertEquals('select * from tablename  where  field = [[field]]  or  field2 = [[field2]]  ', $sql);
    }

    public function testGroup()
    {
        $this->object->startGroup();
        $this->object->addRelation('field', Relation::EQUAL, 'test');
        $this->object->addRelation('field2', Relation::EQUAL, 'test2');
        $this->object->endGroup();
        $this->object->addRelationOr('field3', Relation::EQUAL, 'test3');
        $this->assertEquals(
            "/anydataset/row[ ( field[@name='field'] = 'test'  and field[@name='field2'] = 'test2' ) or field[@name='field3'] = 'test3' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $params = null;
        $returnFields = '*';
        $sql = $this->object->format(
            new IteratorFilterSqlFormatter(),
            'tablename',
            $params,
            $returnFields
        );
        $this->assertEquals(['field' => 'test', 'field2' => 'test2', 'field3' => 'test3'], $params);
        $this->assertEquals(
            'select * from tablename  where  (  field = [[field]]  and  field2 = [[field2]] ) or  field3 = [[field3]]  ',
            $sql
        );
    }
}
