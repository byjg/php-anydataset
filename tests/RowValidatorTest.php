<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Core\RowValidator;
use PHPUnit\Framework\TestCase;

require_once "Sample/ModelPublic.php";
require_once "Sample/ModelGetter.php";
require_once "Sample/ModelPropertyPattern.php";

class RowValidatorTest extends TestCase
{

    /**
     * @var Row
     */
    protected $row1;
    protected $row2;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->row1 = new Row([
            "field1" => 10,
            "field2" => "test",
            "field3" => 20.30,
            "field4" => "2021-11-20"
        ]);
        $this->row2 = new Row([
            "field1" => "30",
            "field3" => "b",
            "field4" => "2021-11-xx"
        ]);
    }

    public function testRequired()
    {
        $validator = RowValidator::getInstance()
            ->requiredFields(["field1", "field2"]);

        $this->assertSame([], $validator->validate($this->row1));
        $this->assertSame(["field2 is required"], $validator->validate($this->row2));
    }

    public function testNumeric()
    {
        $validator = RowValidator::getInstance()
            ->numericFields(["field1", "field3"]);

        $this->assertSame([], $validator->validate($this->row1));
        $this->assertSame(["field3 needs to be a number"], $validator->validate($this->row2));
    }

    public function testRegex()
    {
        $validator = RowValidator::getInstance()
            ->regexValidation("field4", '/\d{4}-\d{2}-\d{2}/');

        $this->assertSame([], $validator->validate($this->row1));
        $this->assertSame(["Regex expression for field4 doesn't match"], $validator->validate($this->row2));
    }

    public function testCustom()
    {
        $validator = RowValidator::getInstance()
            ->customValidation("field1", function($value) {
                if ($value != 10) {
                    return "Value should be 10, but $value was found.";
                }
            });

        $this->assertSame([], $validator->validate($this->row1));
        $this->assertSame(["Value should be 10, but 30 was found."], $validator->validate($this->row2));
    }
}
