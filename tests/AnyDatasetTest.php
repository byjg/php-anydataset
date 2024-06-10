<?php

namespace Tests;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Enum\Relation;
use ByJG\AnyDataset\Core\Formatter\JsonFormatter;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\XmlUtil\XmlDocument;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml;

class AnyDatasetTest extends TestCase
{

    const SAMPLE_DIR = __DIR__ . "/Sample/";
    
    /**
     * @var AnyDataset
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new AnyDataset();
    }

    public function testConstructorString()
    {
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');
        $this->assertEquals(2, count($anydata->getIterator()->toArray()));
        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());

        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample.anydata.xml');
        $this->assertEquals(2, count($anydata->getIterator()->toArray()));
        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());

        $anydataMem = new AnyDataset("php://memory");
        $anydataMem->import($anydata->getIterator());
        $this->assertEquals(2, count($anydataMem->getIterator()->toArray()));
        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
        ], $anydata->getIterator()->toArray());
        $anydataMem->save();
    }

    public function testXML()
    {
        $this->object->appendRow();
        $this->object->addField('field', 'value');

        $xmlDom = new XmlDocument(
                '<?xml version="1.0" encoding="utf-8"?>'
                . '<anydataset>'
                . '<row>'
                . '<field name="field">value</field>'
                . '</row>'
                . '</anydataset>'
        );
        $xmlDomValidate = new XmlDocument($this->object->xml());

        $this->assertEquals($xmlDom, $xmlDomValidate);
    }

    public function testXMFormatter()
    {
        $this->object->appendRow();
        $this->object->addField('field', 'value');

        $xmlDom = new XmlDocument(
                '<?xml version="1.0" encoding="utf-8"?>'
                . '<anydataset>'
                . '<row>'
                . '<field name="field">value</field>'
                . '</row>'
                . '</anydataset>'
        );

        $formatter = new XmlFormatter($this->object->getIterator());
        $this->assertEquals($xmlDom->DOMNode(), $formatter->raw());
    }

    public function testJsonFormatter()
    {
        $this->object->appendRow();
        $this->object->addField('field', 'value');
        $this->object->appendRow();
        $this->object->addField('field', 'value2');

        $jsonText = '[{"field":"value"},{"field":"value2"}]';

        $formatter = new JsonFormatter($this->object->getIterator());
        $this->assertEquals($jsonText, $formatter->toText());
    }

    public function testSave()
    {
        $filename = sys_get_temp_dir() . '/testsave.xml';

        // Guarantee that file does not exists
        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));

        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');
        $anydata->save($filename);

        $proof = new AnyDataset($filename);
        $this->assertEquals(2, count($proof->getIterator()->toArray()));
        $proof->appendRow();
        $proof->addField('field1', 'OK');
        $proof->save();

        $proof2 = new AnyDataset($filename);
        $this->assertEquals(
            $proof->getIterator()->toArray(),
            $proof2->getIterator()->toArray()
        );

        unlink($filename);
    }

    public function testAppendRow()
    {
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(0, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(1, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(2, $qtd);
    }

    public function testImport()
    {
        // Read sample
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');

        // Append a row
        $this->object->appendRow(['field1' => '123']);

        // Import
        $this->object->import($anydata->getIterator());
        $this->assertEquals(3, count($this->object->getIterator()->toArray()));

        $this->assertEquals([
            [
                "field1" => "123"
            ],
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $this->object->getIterator()->toArray());
    }

    public function testInsertRowBefore()
    {
        // Read sample
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');

        // Append a row
        $anydata->insertRowBefore(1, ['field1' => '123']);

        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "123"
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testRemoveRow()
    {
        // Read sample
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');
        $anydata->removeRow(0);

        $this->assertEquals([
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testRemoveRow_1()
    {
        // Read sample
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');
        $anydata->removeRow(1);

        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testAddField()
    {
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(0, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(1, $qtd);

        $this->object->addField('newfield', 'value');

        $this->assertEquals([
            [
                "newfield" => "value",
            ],
            ], $this->object->getIterator()->toArray());
    }

    public function testGetArray()
    {
        // Read sample
        $anydata = new AnyDataset(self::SAMPLE_DIR . 'sample');

        $array = $anydata->getArray('field1');

        $this->assertEquals([
            'value1',
            'othervalue1'
            ], $array);
    }

    public function testSort()
    {
        $this->object->appendRow(['name' => 'joao', 'age' => 41]);
        $this->object->appendRow(['name' => 'fernanda', 'age' => 45]);
        $this->object->appendRow(['name' => 'jf', 'age' => 15]);
        $this->object->appendRow(['name' => 'jg jr', 'age' => 4]);

        $this->assertEquals([
            ['name' => 'joao', 'age' => 41],
            ['name' => 'fernanda', 'age' => 45],
            ['name' => 'jf', 'age' => 15],
            ['name' => 'jg jr', 'age' => 4]
            ], $this->object->getIterator()->toArray());

        $this->object->sort('age');

        $this->assertEquals([
            ['name' => 'jg jr', 'age' => 4],
            ['name' => 'jf', 'age' => 15],
            ['name' => 'joao', 'age' => 41],
            ['name' => 'fernanda', 'age' => 45],
            ], $this->object->getIterator()->toArray());
    }

    public function testIteratorFilter()
    {
        $this->object->appendRow(['name' => 'joao', 'age' => 41]);
        $this->object->appendRow(['name' => 'fernanda', 'age' => 45]);
        $this->object->appendRow(['name' => 'jf', 'age' => 15]);
        $this->object->appendRow(['name' => 'jg jr', 'age' => 4]);

        $filter = IteratorFilter::getInstance()
            ->addRelation("age", Relation::LESS_THAN, 40);

        $this->assertEquals([
            ['name' => 'jf', 'age' => 15],
            ['name' => 'jg jr', 'age' => 4]
        ], $this->object->getIterator($filter)->toArray());

        $this->assertEquals([
            ['name' => 'jf', 'age' => 15],
            ['name' => 'jg jr', 'age' => 4]
        ], $this->object->getIterator()->withFilter($filter)->toArray());

    }

    public function testToArrayFields()
    {
        $anydataset = new AnyDataset();
        $anydataset->appendRow([
            "field1" => "value1",
            "field2" => "value2",
            "field3" => "value3",
            "field4" => "value4",
        ]);

        $anydataset->appendRow([
            "field1" => "1",
            "field2" => "2",
            "field4" => "4",
        ]);

        $iterator = $anydataset->getIterator()->toArray(["field1", "field3"]);
        $this->assertEquals([
            [
                "field1" => "value1",
                "field3" => "value3" 
            ],
            [
                "field1" => "1",
                "field3" => "" 
            ],
        ], $iterator);
    }
}
