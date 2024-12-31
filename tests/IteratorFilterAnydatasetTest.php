<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\Row;
use PHPUnit\Framework\TestCase;

class IteratorFilterAnydatasetTest extends TestCase
{

    /**
     * @var IteratorFilter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new IteratorFilter();
    }

    public function testMatch()
    {

        $collection = [
            $row1 = new Row(
                [
                    'id'   => 1,
                    'field' => 'value1',
                    'field2' => 'value2',
                    'val' => 50,
                ]
            ),
            $row2 = new Row(
                [
                    'id'   => 2,
                    'field' => 'other1',
                    'field2' => 'other2',
                    'val' => 80,
                ]
            ),
            $row3 = new Row(
                [
                    'id'   => 3,
                    'field' => 'last1',
                    'field2' => 'last2',
                    'val' => 30,
                ]
            ),
            $row4 = new Row(
                [
                    'id'   => 4,
                    'field' => 'xy',
                    'field2' => 'zy',
                    'val' => 10,
                ]
            ),

            $row5 = new Row(
                [
                    'id'   => 5,
                    'field' => 'ab',
                    'field2' => 'ba',
                    'val' => null,
                ]
            ),
        ];

        $this->assertEquals($collection, $this->object->match($collection));

        $this->object->and('field2', Relation::EQUAL, 'other2');
        $this->assertEquals([$row2], $this->object->match($collection));

        $this->object->or('field', Relation::EQUAL, 'last1');
        $this->assertEquals([$row2, $row3], $this->object->match($collection));


        //------------------------

        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::EQUAL, 'last1');
        $this->object->and('field2', Relation::EQUAL, 'last2');
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Greater Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GREATER_THAN, 50);
        $this->assertEquals([$row2], $this->object->match($collection));

        // Test Less Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LESS_THAN, 50);
        $this->assertEquals([$row3, $row4, $row5], $this->object->match($collection));

        // Test Greater or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GREATER_OR_EQUAL_THAN, 50);
        $this->assertEquals([$row1, $row2], $this->object->match($collection));

        // Test Less or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LESS_OR_EQUAL_THAN, 50);
        $this->assertEquals([$row1, $row3, $row4, $row5], $this->object->match($collection));

        // Test Not Equal
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NOT_EQUAL, 50);
        $this->assertEquals([$row2, $row3, $row4, $row5], $this->object->match($collection));

        // Test Starts With
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::STARTS_WITH, 'la');
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Contains
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::CONTAINS, '1');
        $this->assertEquals([$row1, $row2, $row3], $this->object->match($collection));

        // Test In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::IN, [10, 30, 50]);
        $this->assertEquals([$row1, $row3, $row4], $this->object->match($collection));

        // Test Not In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NOT_IN, [10, 30, 50]);
        $this->assertEquals([$row2, $row5], $this->object->match($collection));

        // Test Is Null
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::IS_NULL);
        $this->assertEquals([$row5], $this->object->match($collection));

        // Test Is Not Null
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::IS_NOT_NULL);
        $this->assertEquals([$row1, $row2, $row3, $row4], $this->object->match($collection));
    }


}
