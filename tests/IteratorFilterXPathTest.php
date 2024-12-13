<?php

namespace Tests;

use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\IteratorFilterXPathFormatter;
use PHPUnit\Framework\TestCase;

class IteratorFilterXPathTest extends TestCase
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

    public function testGetXPath()
    {
        $this->assertEquals(
            '/anydataset/row',
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->and('field', Relation::EQUAL, 'test');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->and('field2', Relation::GREATER_OR_EQUAL_THAN, 'test2');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  and field[@name='field2'] >= 'test2' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );

        $this->object->and('field3', Relation::CONTAINS, 'test3');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  and field[@name='field2'] >= 'test2'  and  contains(field[@name='field3'] ,  'test3' ) ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );
    }

    public function testAddRelationOr()
    {
        $this->object->and('field', Relation::EQUAL, 'test');
        $this->object->or('field2', Relation::EQUAL, 'test2');
        $this->assertEquals(
            "/anydataset/row[field[@name='field'] = 'test'  or field[@name='field2'] = 'test2' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );
    }

    public function testGroup()
    {
        $this->object->startGroup('field', Relation::EQUAL, 'test');
        $this->object->and('field2', Relation::EQUAL, 'test2');
        $this->object->endGroup();
        $this->object->or('field3', Relation::EQUAL, 'test3');
        $this->assertEquals(
            "/anydataset/row[(field[@name='field'] = 'test'  and field[@name='field2'] = 'test2' ) or field[@name='field3'] = 'test3' ]",
            $this->object->format(new IteratorFilterXPathFormatter())
        );
    }

    public function testIn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->object->and('field', Relation::IN, ['test', 'test2']);
        $this->object->format(new IteratorFilterXPathFormatter());
    }

    public function testNotIn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->object->and('field', Relation::NOT_IN, ['test', 'test2']);
        $this->object->format(new IteratorFilterXPathFormatter());
    }
}
