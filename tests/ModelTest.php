<?php

namespace Tests;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\Serializer\Serialize;
use PHPUnit\Framework\TestCase;
use Tests\Sample\SampleModel;

class ModelTest extends TestCase
{
    public function testBindSingleRow()
    {
        $sr = new \ByJG\AnyDataset\Core\Row();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");

        $object = new SampleModel($sr->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    public function testBindIterator()
    {
        $anydata = new AnyDataset();

        $sr = new \ByJG\AnyDataset\Core\Row();
        $sr->addField("id", 10);
        $sr->addField("name", "Testing");
        $anydata->appendRow($sr);

        $object = new SampleModel($anydata->getIterator()->moveNext()->toArray());

        $this->assertEquals(10, $object->Id);
        $this->assertEquals("Testing", $object->getName());
    }

    public function testBind_Iterator2()
    {
        $anydata = new AnyDataset();
        $anydata->addField('Id', 10);
        $anydata->addField('Name', 'Joao');
        $anydata->appendRow();
        $anydata->addField('Id', 20);
        $anydata->addField('Name', 'Gilberto');

        $object1 = new SampleModel();
        $object1->copyFrom( $anydata->getIterator()->moveNext()->toArray() );
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

        $object = Serialize::from($iterator->toArray());
        $result = $object->toArray();

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
