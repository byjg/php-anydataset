<?php

namespace Tests;

use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Core\RowOutput;
use PHPUnit\Framework\TestCase;

class RowOutputTest extends TestCase
{

    public function testFormatPattern()
    {
        $row1 = new Row([
            "field1" => 10,
            "field2" => "test",
            "field3" => 20.30,
            "field4" => "2021-11-20"
        ]);

        $row2 = new Row([
            "field1" => 1,
            "field2" => "OK",
            "field3" => 3,
        ]);

        $formatter = RowOutput::getInstance()
            ->addFormat("field1", "Test {field1}")
            ->addFormat("field2", "{.}: Showing {} and {field3}");

        $this->assertEquals("Test 10", $formatter->print($row1, "field1"));
        $this->assertEquals("field2: Showing test and 20.3", $formatter->print($row1, "field2"));
        $this->assertEquals("20.30", $formatter->print($row1, "field3"));

        $this->assertEquals("Test 1", $formatter->print($row2, "field1"));
        $this->assertEquals("field2: Showing OK and 3", $formatter->print($row2, "field2"));
        $this->assertEquals("3", $formatter->print($row2, "field3"));
    }

    public function testFormatCustom()
    {
        $row = new Row([
            "field1" => 10,
            "field2" => "test",
            "field3" => 20.30,
            "field4" => "2021-11-20"
        ]);

        $formatter = RowOutput::getInstance()
            ->addCustomFormat("field1", function ($row, $field, $value) {
                return $value * 4;
            })
            ->addCustomFormat("field3", function ($row, $field, $value) {
                return "$field x 3 = " . ($value * 3);
            });

        $this->assertEquals("40", $formatter->print($row, "field1"));
        $this->assertEquals("field3 x 3 = 60.9", $formatter->print($row, "field3"));
        $this->assertEquals("2021-11-20", $formatter->print($row, "field4"));
    }

    public function testApply()
    {
        $row = new Row([
            "field1" => 10,
            "field2" => "test",
            "field3" => 20.30,
            "field4" => "2021-11-20"
        ]);

        $formatter = RowOutput::getInstance()
            ->addFormat("field1", "Value: {field1}")
            ->addCustomFormat("field3", function ($row, $field, $value) {
                return "$field x 3 = " . ($value * 3);
            });

        $this->assertEquals("10", $row->get("field1"));
        $this->assertEquals("test", $row->get("field2"));
        $this->assertEquals("20.30", $row->get("field3"));
        $this->assertEquals("2021-11-20", $row->get("field4"));

        $newRow = $formatter->apply($row);
    
        $this->assertEquals("Value: 10", $newRow->get("field1"));
        $this->assertEquals("test", $newRow->get("field2"));
        $this->assertEquals("field3 x 3 = 60.9", $newRow->get("field3"));
        $this->assertEquals("2021-11-20", $newRow->get("field4"));
    }
}
