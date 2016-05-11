<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\Serializer\SerializerObject;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testBindSingleRow()
    {
        $sr = new \ByJG\AnyDataset\Repository\SingleRow();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");

        $object = new \AnyDataSet\Tests\Sample\SampleModel($sr->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    public function testBindIterator()
    {
        $anydata = new \ByJG\AnyDataset\Repository\AnyDataset();

        $sr = new \ByJG\AnyDataset\Repository\SingleRow();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");
        $anydata->appendRow($sr);

        $object = new \AnyDataSet\Tests\Sample\SampleModel($anydata->getIterator()->moveNext()->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    /**
     * @covers ByJG\Serializer\BinderObject::bind
     */
    public function testBind_Iterator2()
    {
        $anydata = new \ByJG\AnyDataset\Repository\AnyDataset();
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
