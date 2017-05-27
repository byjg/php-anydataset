<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\Serializer\SerializerObject;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class ModelTest extends \PHPUnit\Framework\TestCase
{
    public function testBindSingleRow()
    {
        $sr = new \ByJG\AnyDataset\Dataset\Row();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");

        $object = new \AnyDataSet\Tests\Sample\SampleModel($sr->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    public function testBindIterator()
    {
        $anydata = new \ByJG\AnyDataset\Dataset\AnyDataset();

        $sr = new \ByJG\AnyDataset\Dataset\Row();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");
        $anydata->appendRow($sr);

        $object = new \AnyDataSet\Tests\Sample\SampleModel($anydata->getIterator()->moveNext()->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    public function testBind_Iterator2()
    {
        $anydata = new \ByJG\AnyDataset\Dataset\AnyDataset();
        $anydata->addField('Id', 10);
        $anydata->addField('Name', 'Joao');
        $anydata->appendRow();
        $anydata->addField('Id', 20);
        $anydata->addField('Name', 'Gilberto');

        $object1 = new \AnyDataSet\Tests\Sample\SampleModel();
        $object1->bind( $anydata->getIterator()->moveNext()->toArray() );
        $this->assertEquals(10, $object1->Id);
        $this->assertEquals('Joao', $object1->getName());
    }

    public function testIterator()
    {
        $model = new AnyDataset();
        $model->AddField("id", 10);
        $model->AddField("name", 'Testing');

        $model->appendRow();
        $model->AddField("id", 20);
        $model->AddField("name", 'Other');

        $iterator = $model->getIterator();

        $object = new SerializerObject($iterator->toArray());
        $result = $object->build();

        $this->assertEquals(
            [
                [
                    "id" => 10,
                    "name" => "Testing"
                ],
                [
                    "id" => 20,
                    "name" => "Other"
                ]
            ],
            $result
        );
    }

}
