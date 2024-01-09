<?php

namespace Tests;

use ByJG\AnyDataset\Core\Formatter\JsonFormatter;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;
use ByJG\AnyDataset\Core\Row;
use PHPUnit\Framework\TestCase;
use ByJG\Util\XmlUtil;
use stdClass;
use Tests\Sample\ModelGetter;
use Tests\Sample\ModelPropertyPattern;
use Tests\Sample\ModelPublic;

class RowTest extends TestCase
{

    /**
     * @var Row
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Row;
    }

    protected function fill()
    {
        $this->object->addField('field1', '10');
        $this->object->addField('field1', '20');
        $this->object->addField('field1', '30');
        $this->object->addField('field2', '40');
        $this->object->acceptChanges();
    }

    public function testAddField()
    {
        $this->object->addField('field1', '10');
        $this->assertEquals(
            array(
            'field1' => 10
            ),
            $this->object->toArray()
        );

        $this->object->addField('field1', '20');
        $this->assertEquals(
            array(
            'field1' => array(10, 20)
            ),
            $this->object->toArray()
        );

        $this->object->addField('field1', '30');
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30)
            ),
            $this->object->toArray()
        );

        $this->object->addField('field2', '40');
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30),
            'field2' => 40
            ),
            $this->object->toArray()
        );

        $this->object->addField('field1', '20');
        $this->assertEquals(
            array(
            'field1' => array(10, 20, 30, 20),
            'field2' => 40
            ),
            $this->object->toArray()
        );
    }

    public function testGetField()
    {
        $this->fill();

        $this->assertEquals(10, $this->object->get('field1'));
        $this->assertEquals(10, $this->object->get('field1'));  // Test it again, because is an array
        $this->assertEquals(40, $this->object->get('field2'));
        $this->assertEquals(null, $this->object->get('not-exists'));
    }

    public function testGetFieldArray()
    {
        $this->fill();

        $this->assertEquals(array(10, 20, 30), $this->object->getAsArray('field1'));
        $this->assertEquals(array(40), $this->object->getAsArray('field2'));

        $this->object->addField('field3', '');
        $this->object->acceptChanges();

        $this->assertEquals(array(), $this->object->getAsArray('field3'));
    }

    public function testGetFieldNames()
    {
        $this->fill();

        $this->assertEquals(array('field1', 'field2'), $this->object->getFieldNames());
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

        $this->object->removeField('field1');
        $this->assertEquals(null, $this->object->get('field1'));
        $this->assertEquals(40, $this->object->get('field2'));
    }

    public function testRemoveFieldName2()
    {
        $this->fill();

        $this->object->removeField('field2');
        $this->assertEquals(10, $this->object->get('field1'));
        $this->assertEquals(null, $this->object->get('field2'));
    }

    public function testRemoveFieldNameValue()
    {
        $this->fill();

        $this->object->removeValue('field1', 20);
        $this->assertEquals(array(10, 30), $this->object->getAsArray('field1'));

        $this->object->removeValue('field2', 100);
        $this->assertEquals(40, $this->object->get('field2')); // Element was not removed

        $this->object->removeValue('field2', 40);
        $this->assertEquals(null, $this->object->get('field2'));
    }

    public function testSetFieldValue()
    {
        $this->fill();

        $this->object->replaceValue('field2', 100, 200);
        $this->assertEquals(40, $this->object->get('field2')); // Element was not changed

        $this->object->replaceValue('field2', 40, 200);
        $this->assertEquals(200, $this->object->get('field2'));

        $this->object->replaceValue('field1', 500, 190);
        $this->assertEquals(array(10, 20, 30), $this->object->getAsArray('field1')); // Element was not changed

        $this->object->replaceValue('field1', 20, 190);
        $this->assertEquals(array(10, 190, 30), $this->object->getAsArray('field1'));
    }

    public function testGetDomObject()
    {
        $this->fill();

        $dom = XmlUtil::CreateXmlDocumentFromStr(
            "<row>"
            . "<field name='field1'>10</field>"
            . "<field name='field1'>20</field>"
            . "<field name='field1'>30</field>"
            . "<field name='field2'>40</field>"
            . "</row>"
        );

        $formatter = (new XmlFormatter($this->object))->raw();

        $this->assertEquals($dom, $formatter);
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
            array('field1' => array(10, 20, 30), 'field2' => 40),
            $this->object->getAsRaw()
        );
    }

    public function testHasChanges()
    {
        $this->fill();

        $this->assertFalse($this->object->hasChanges());
        $this->object->set('field2', 150);
        $this->assertTrue($this->object->hasChanges());
    }

    public function testAcceptChanges()
    {
        $this->fill();

        $this->object->set('field2', 150);
        $this->assertEquals(array('field1' => array(10, 20, 30), 'field2' => 40), $this->object->getAsRaw());
        $this->object->acceptChanges();
        $this->assertEquals(array('field1' => array(10, 20, 30), 'field2' => 150), $this->object->getAsRaw());
    }

    public function testRejectChanges()
    {
        $this->fill();

        $this->object->set('field2', 150);
        $this->assertEquals(array('field1' => array(10, 20, 30), 'field2' => 150), $this->object->toArray());
        $this->assertEquals(150, $this->object->get('field2'));
        $this->object->rejectChanges();
        $this->assertEquals(array('field1' => array(10, 20, 30), 'field2' => 40), $this->object->toArray());
        $this->assertEquals(40, $this->object->get('field2'));
    }

    public function testConstructor_ModelPublic()
    {
        $model = new ModelPublic(10, 'Testing');

        $sr = new Row($model);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals(['Id' => 10, 'Name' => 'Testing'], $sr->toArray());
    }

    public function testConstructor_ModelGetter()
    {
        $model = new ModelGetter(10, 'Testing');

        $sr = new Row($model);

        $this->assertEquals(10, $sr->get("Id"));
        $this->assertEquals("Testing", $sr->get("Name"));
        $this->assertEquals(['Id' => 10, 'Name' => 'Testing'], $sr->toArray());
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
    }

    public function testCaseSensitive_1()
    {
        $row = new Row([
            "fieldA" => "test",
            "fieldB" => "new test"
        ]);

        $this->assertTrue($row->isFieldNameCaseSensitive());

        $this->assertEquals("test", $row->get("fieldA"));
        $this->assertEquals("new test", $row->get("fieldB"));

        $this->assertNull($row->get("fielda"));
        $this->assertNull($row->get("fieldb"));

        $row->enableFieldNameCaseInSensitive();

        $this->assertFalse($row->isFieldNameCaseSensitive());

        $this->assertEquals("test", $row->get("fielda"));
        $this->assertEquals("new test", $row->get("FiEldb"));
    }

    public function testCaseSensitive_2()
    {
        $row = new Row([
            "fieldA" => "test",
            "fieldB" => "new test"
        ]);

        $row->set("FIELDA", "a");
        $this->assertEquals("test", $row->get("fieldA"));
        $this->assertEquals("a", $row->get("FIELDA"));

        $row->enableFieldNameCaseInSensitive();
        // When enable case insentive, the last field name overwrite the value
        $this->assertEquals("a", $row->get("FieLda")); 
        
        $row->set("FIELDB", "new value");
        $this->assertEquals("new value", $row->get("FieLdB")); 

        $this->assertFalse($row->fieldExists("DelEteME")); 
        $row->addField("DELETEME", "true");
        $this->assertTrue($row->fieldExists("DelEteME")); 

        $row->removeField("dELeTEme");
        $this->assertFalse($row->fieldExists("DelEteME")); 
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
