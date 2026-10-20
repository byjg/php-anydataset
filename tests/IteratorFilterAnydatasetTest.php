<?php

namespace Tests;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\Row;
use PHPUnit\Framework\TestCase;

class IteratorFilterAnydatasetTest extends TestCase
{

    /**
     * @var IteratorFilter
     */
    protected IteratorFilter $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->object = new IteratorFilter();
    }

    public function testMatch()
    {
        $collection = [
            $row1 = Row::factory(
                [
                    'id'   => 1,
                    'field' => 'value1',
                    'field2' => 'value2',
                    'val' => 50,
                ]
            ),
            $row2 = Row::factory(
                [
                    'id'   => 2,
                    'field' => 'other1',
                    'field2' => 'other2',
                    'val' => 80,
                ]
            ),
            $row3 = Row::factory(
                [
                    'id'   => 3,
                    'field' => 'last1',
                    'field2' => 'last2',
                    'val' => 30,
                ]
            ),
            $row4 = Row::factory(
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

        $this->object->and('field2', Relation::Equal, 'other2');
        $this->assertEquals([$row2], $this->object->match($collection));

        $this->object->or('field', Relation::Equal, 'last1');
        $this->assertEquals([$row2, $row3], $this->object->match($collection));


        //------------------------

        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::Equal, 'last1');
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Greater Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GreaterThan, 50);
        $this->assertEquals([$row2], $this->object->match($collection));

        // Test Less Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LessThan, 50);
        $this->assertEquals([$row3, $row4, $row5], $this->object->match($collection));

        // Test Greater or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GreaterThanOrEqualTo, 50);
        $this->assertEquals([$row1, $row2], $this->object->match($collection));

        // Test Less or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LessThanOrEqualTo, 50);
        $this->assertEquals([$row1, $row3, $row4, $row5], $this->object->match($collection));

        // Test Not Equal
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NotEqual, 50);
        $this->assertEquals([$row2, $row3, $row4, $row5], $this->object->match($collection));

        // Test Starts With
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::StartsWith, 'la');
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Contains
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::Contains, '1');
        $this->assertEquals([$row1, $row2, $row3], $this->object->match($collection));

        // Test In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::In, [10, 30, 50]);
        $this->assertEquals([$row1, $row3, $row4], $this->object->match($collection));

        // Test Not In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NotIn, [10, 30, 50]);
        $this->assertEquals([$row2, $row5], $this->object->match($collection));

        // Test Group 1
        $this->object = new IteratorFilter();
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->assertEquals([$row1, $row3], $this->object->match($collection));

        // Test Group 2
        $this->object = new IteratorFilter();
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Group 3
        $this->object = new IteratorFilter();
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->assertEquals([$row3], $this->object->match($collection));

        // Test Is Null
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::IsNull);
        $this->assertEquals([$row5], $this->object->match($collection));

        // Test Is Not Null
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::IsNotNull);
        $this->assertEquals([$row1, $row2, $row3, $row4], $this->object->match($collection));
    }

    public function testGetIterator()
    {

        $row1 = [
            'id'   => 1,
            'field' => 'value1',
            'field2' => 'value2',
            'val' => 50,
        ];
        $row2 = [
            'id'   => 2,
            'field' => 'other1',
            'field2' => 'other2',
            'val' => 80,
        ];
        $row3 = [
            'id'   => 3,
            'field' => 'last1',
            'field2' => 'last2',
            'val' => 30,
        ];
        $row4 = [
            'id'   => 4,
            'field' => 'xy',
            'field2' => 'zy',
            'val' => 10,
        ];

        $anydataset = new AnyDataset();
        $anydataset->appendRow($row1);
        $anydataset->appendRow($row2);
        $anydataset->appendRow($row3);
        $anydataset->appendRow($row4);

        $this->assertEquals([$row1, $row2, $row3, $row4], $anydataset->getIterator()->toArray());

        $this->object->and('field2', Relation::Equal, 'other2');
        $this->assertEquals([$row2], $anydataset->getIterator($this->object)->toArray());

        $this->object->or('field', Relation::Equal, 'last1');
        $this->assertEquals([$row2, $row3], $anydataset->getIterator($this->object)->toArray());


        //------------------------

        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::Equal, 'last1');
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->assertEquals([$row3], $anydataset->getIterator($this->object)->toArray());

        // Test Greater Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GreaterThan, 50);
        $this->assertEquals([$row2], $anydataset->getIterator($this->object)->toArray());

        // Test Less Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LessThan, 50);
        $this->assertEquals([$row3, $row4], $anydataset->getIterator($this->object)->toArray());

        // Test Greater or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::GreaterThanOrEqualTo, 50);
        $this->assertEquals([$row1, $row2], $anydataset->getIterator($this->object)->toArray());

        // Test Less or Equal Than
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::LessThanOrEqualTo, 50);
        $this->assertEquals([$row1, $row3, $row4], $anydataset->getIterator($this->object)->toArray());

        // Test Not Equal
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NotEqual, 50);
        $this->assertEquals([$row2, $row3, $row4], $anydataset->getIterator($this->object)->toArray());

        // Test Starts With
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::StartsWith, 'la');
        $this->assertEquals([$row3], $anydataset->getIterator($this->object)->toArray());

        // Test Contains
        $this->object = new IteratorFilter();
        $this->object->and('field', Relation::Contains, '1');
        $this->assertEquals([$row1, $row2, $row3], $anydataset->getIterator($this->object)->toArray());

        // Test In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::In, [10, 30, 50]);
        $this->assertEquals([$row1, $row3, $row4], $anydataset->getIterator($this->object)->toArray());

        // Test Not In
        $this->object = new IteratorFilter();
        $this->object->and('val', Relation::NotIn, [10, 30, 50]);
        $this->assertEquals([$row2], $anydataset->getIterator($this->object)->toArray());

        // Test Group 1
        $this->object = new IteratorFilter();
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->assertEquals([$row1, $row3], $anydataset->getIterator($this->object)->toArray());

        // Test Group 2
        $this->object = new IteratorFilter();
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->assertEquals([$row3], $anydataset->getIterator($this->object)->toArray());

        // Test Group 3
        $this->object = new IteratorFilter();
        $this->object->and('field2', Relation::Equal, 'last2');
        $this->object->startGroup('id', Relation::Equal, 1);
        $this->object->or('id', Relation::Equal, 3);
        $this->object->endGroup();
        $this->assertEquals([$row3], $anydataset->getIterator($this->object)->toArray());
    }

}
