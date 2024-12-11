<?php

namespace Tests;

use ByJG\AnyDataset\Core\Formatter\JsonFormatter;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;
use ByJG\AnyDataset\Core\Row;
use ByJG\XmlUtil\XmlDocument;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Sample\ModelGetter;
use Tests\Sample\ModelPropertyPattern;
use Tests\Sample\ModelPublic;

class RowTest extends TestCase
{

    /**
     * @var Row
     */
    protected Row $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Row();
    }

    protected function fill()
    {
        $this->object->set('field1', '10', append: true);
        $this->object->set('field1', '20', append: true);
        $this->object->set('field1', '30', append: true);
        $this->object->set('field2', '40');
    }

    public function testAppend()
    {
        $this->object->set('field1', '10');
        $this->assertEquals(
            array(
            'field1' => 10
            ),
            $this->object->toArray()
        );

        $this->object->set('field1', '20', append: true);
        $this->assertEquals(
            array(
            'field1' => array(10, 20)
            ),
            $this->object->toArray()
        );

        $this->object->set('field1', '30', append: true);
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30)
            ),
            $this->object->toArray()
        );

        $this->object->set('field2', '40', append: true);
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30),
            'field2' => 40
            ),
            $this->object->toArray()
        );

        $this->object->set('field1', '20', append: true);
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30, 20),
            'field2' => 40
            ),
            $this->object->toArray()
        );
    }

    public function testGetFieldArray()
    {
        $this->fill();

        $this->assertEquals(array(10, 20, 30), $this->object->get('field1'));
        $this->assertEquals(40, $this->object->get('field2'));

        $this->object->set('field3', '');

        $this->assertEquals('', $this->object->get('field3'));
        $this->assertNull($this->object->get('field4'));
    }

    public function testGetFieldNames()
    {
        $this->fill();

        $this->assertEquals(array('field1', 'field2'), array_keys($this->object->toArray()));
    }

    public function testSetField()
    {
        $this->fill();

        $this->object->set('field1', 70);
        $this->assertEquals(70, $this->object->get('field1'));

        $this->object->set('field2', 60);
        $this->assertEquals(60, $this->object->get('field2'));

        $this->object->set('field3', 50);
        $this->assertEquals(50, $this->object->get('field3'));
    }

    public function testRemoveFieldName()
    {
        $this->fill();

        $this->assertEquals(["field1" => [10, 20, 30], "field2" => 40], $this->object->toArray());

        $this->object->unset('field1');
        $this->assertEquals(null, $this->object->get('field1'));
        $this->assertEquals(40, $this->object->get('field2'));

        $this->assertEquals(["field2" => 40], $this->object->toArray());
    }

    public function testRemoveFieldName2()
    {
        $this->fill();

        $this->assertEquals(["field1" => [10, 20, 30], "field2" => 40], $this->object->toArray());

        $this->object->unset('field2');
        $this->assertEquals([10, 20, 30], $this->object->get('field1'));
        $this->assertEquals(null, $this->object->get('field2'));

        $this->assertEquals(["field1" => [10, 20, 30]], $this->object->toArray());
    }

    public function testRemoveFieldNameValue()
    {
        $this->fill();

        $this->object->unset('field1', 20);
        $this->assertEquals(array(10, 30), $this->object->get('field1'));

        $this->object->unset('field2', 100);
        $this->assertEquals(40, $this->object->get('field2')); // Element was not removed

        $this->object->unset('field2', 40);
        $this->assertEquals(null, $this->object->get('field2'));
    }

    public function testSetFieldValue()
    {
        $this->fill();

        $this->object->replace('field2', 100, 200);
        $this->assertEquals(40, $this->object->get('field2')); // Element was not changed

        $this->object->replace('field2', 40, 200);
        $this->assertEquals(200, $this->object->get('field2'));

        $this->object->replace('field1', 500, 190);
        $this->assertEquals(array(10, 20, 30), $this->object->get('field1')); // Element was not changed

        $this->object->replace('field1', 20, 190);
        $this->assertEquals(array(10, 190, 30), $this->object->get('field1'));
    }

    public function testGetDomObject()
    {
        $this->fill();

        $dom = new XmlDocument(
            "<row>"
            . "<field name='field1'>10</field>"
            . "<field name='field1'>20</field>"
            . "<field name='field1'>30</field>"
            . "<field name='field2'>40</field>"
            . "</row>"
        );

        $formatter = (new XmlFormatter($this->object))->raw();

        $this->assertEquals($dom->DOMNode(), $formatter);
    }

    public function testGetJson()
    {
        $this->fill();

        $json = new stdClass;
        $json->field1 = [10, 20, 30];
        $json->field2 = '40';

        $formatter = new JsonFormatter($this->object);
        $this->assertEquals($json, $formatter->raw());

        $jsonText = '{"field1":["10","20","30"],"field2":"40"}';
        $this->assertEquals($jsonText, $formatter->toText());
    }

    public function testGetOriginalRawFormat()
    {
        $this->fill();

        $this->object->set('field2', 150);
        $this->assertEquals(
            array('field1' => array(10, 20, 30), 'field2' => 150),
            $this->object->entity()
        );
    }

    public function testConstructor_ModelPublic()
    {
        $model = new ModelPublic(10, 'Testing');

        $sr = new Row($model);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals(['Id' => 10, 'Name' => 'Testing'], $sr->toArray());

        $sr->set("Id", 20);
        $sr->set("Name", "New Name");

        $this->assertEquals(20, $sr->get("Id"));
        $this->assertEquals("New Name", $sr->get("Name"));
        $this->assertEquals(['Id' => 20, 'Name' => 'New Name'], $sr->toArray());
        $this->assertEquals(new ModelPublic(20, "New Name"), $sr->entity());
    }

    public function testConstructor_ModelGetter()
    {
        $model = new ModelGetter(10, 'Testing');

        $sr = new Row($model);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals(['Id' => 10, 'Name' => 'Testing'], $sr->toArray());

        $sr->set("Id", 20);
        $sr->set("Name", "New Name");

        $this->assertEquals(20, $sr->get("Id"));
        $this->assertEquals("New Name", $sr->get("Name"));
        $this->assertEquals(['Id' => 20, 'Name' => 'New Name'], $sr->toArray());
        $this->assertEquals(new ModelGetter(20, "New Name"), $sr->entity());
    }

    public function testConstructor_stdClass()
    {
        $model = new stdClass();
        $model->Id = 10;
        $model->Name = "Testing";

        $sr = new Row($model);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals(['Id' => 10, 'Name' => 'Testing'], $sr->toArray());

        $sr->set("Id", 20);
        $sr->set("Name", "New Name");

        $this->assertEquals(20, $sr->get("Id"));
        $this->assertEquals("New Name", $sr->get("Name"));
        $this->assertEquals(['Id' => 20, 'Name' => 'New Name'], $sr->toArray());
        $this->assertEquals((object) ['Id' => 20, 'Name' => 'New Name'], $sr->entity());
    }

    public function testConstructor_Array()
    {
        $array = array("Id" => 10, "Name" => "Testing");

        $sr = new Row($array);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals($array, $sr->toArray());
    }

    public function testConstructor_PropertyPattern()
    {
        $model = new ModelPropertyPattern();
        $model->setIdModel(10);
        $model->setClientName("Testing");

        $sr = new Row($model);

        // Important to note:
        // The property is _Id_Model, but is changed to "set/get IdModel" throught PropertyName
        // Because this, the field is Id_Model instead IdModel
        $this->assertEquals(10, $sr->get("IdModel"));
        $this->assertEquals("Testing", $sr->get("ClientName"));
        $this->assertEquals(['IdModel' => 10, 'ClientName' => 'Testing'], $sr->toArray());

        $sr->set("IdModel", 20);
        $sr->set("ClientName", "New Name");

        $this->assertEquals(20, $sr->get("IdModel"));
        $this->assertEquals("New Name", $sr->get("ClientName"));
        $this->assertEquals(['IdModel' => 20, 'ClientName' => 'New Name'], $sr->toArray());

        $expected = new ModelPropertyPattern();
        $expected->setIdModel(20);
        $expected->setClientName("New Name");
        $this->assertEquals($expected, $sr->entity());
    }

    public function testToArrayFields()
    {
        $row = new Row([
            "field1" => "value1",
            "field2" => "value2",
            "field3" => "value3",
            "field4" => "value4",
        ]);

        $iterator = $row->toArray(["field1", "field3"]);
        $this->assertEquals([
            "field1" => "value1",
            "field3" => "value3" 
        ], $iterator);
    }
}
