<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\IteratorFilterXPathFormatter;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Core\Enum\Relation;
use PHPUnit\Framework\TestCase;

class IteratorFilterTest extends TestCase
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
    }
}
