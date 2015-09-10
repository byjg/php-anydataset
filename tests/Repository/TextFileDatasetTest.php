<?php

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
use ByJG\AnyDataset\Repository\TextFileDataset;

/**
 * NOTE: The class name must end with "Test" suffix.
 */
class TextFileDatasetTest extends PHPUnit_Framework_TestCase
{

    protected static $fieldNames;
    protected static $fileName_Unix = "";
    protected static $fileName_Windows = "";
    protected static $fileName_MacClassic = "";
    protected static $fileName_BlankLine = "";

    const RemoteURL = "http://www.xmlnuke.com/site/";

    public static function setUpBeforeClass()
    {
        self::$fileName_Unix = sys_get_temp_dir() . "/textfiletest-unix.csv";
        self::$fileName_Windows = sys_get_temp_dir() . "/textfiletest-windows.csv";
        self::$fileName_MacClassic = sys_get_temp_dir() . "/textfiletest-mac.csv";
        self::$fileName_BlankLine = sys_get_temp_dir() . "/textfiletest-bl.csv";

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\n";
        }
        file_put_contents(self::$fileName_Unix, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\r\n";
        }
        file_put_contents(self::$fileName_Windows, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            $text .= "$i;STRING$i;VALUE$i\r";
        }
        file_put_contents(self::$fileName_MacClassic, $text);

        $text = "";
        for ($i = 1; $i <= 2000; $i++) {
            if (rand(0, 10) < 3) {
                $text .= "\n";
            }
            $text .= "$i;STRING$i;VALUE$i\n";
        }
        file_put_contents(self::$fileName_BlankLine, $text);

        // A lot of extras fields
        self::$fieldNames = array();
        for ($i = 1; $i < 30; $i++) {
            self::$fieldNames[] = "field$i";
        }
    }

    public static function tearDownAfterClass()
    {
        unlink(self::$fileName_Unix);
        unlink(self::$fileName_Windows);
        unlink(self::$fileName_MacClassic);
        unlink(self::$fileName_BlankLine);
    }

    // Run before each test case
    function setUp()
    {
        // Nothing Here
    }

    // Run end each test case
    function teardown()
    {
        // Nothing Here
    }

    function test_createTextFileData_Unix()
    {
        $txtFile = new TextFileDataset(self::$fileName_Unix, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface, "Resultant object must be an interator");
        $this->assertTrue($txtIterator->hasNext(), "hasNext() method must be true");
        $this->assertTrue($txtIterator->Count() == -1, "Count() does not return anything by default.");
        $this->assertRowCount($txtIterator, 2000);
    }

    function test_createTextFileData_Windows()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    function test_createTextFileData_MacClassic()
    {
        $txtFile = new TextFileDataset(self::$fileName_MacClassic, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    function test_createTextFileData_BlankLine()
    {
        $txtFile = new TextFileDataset(self::$fileName_BlankLine, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $this->assertTrue($txtIterator instanceof IteratorInterface);
        $this->assertTrue($txtIterator->hasNext());
        $this->assertEquals($txtIterator->Count(), -1);
        $this->assertRowCount($txtIterator, 2000);
    }

    function test_navigateTextIterator_Unix()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_Windows()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_MacClassic()
    {
        $txtFile = new TextFileDataset(self::$fileName_Windows, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_BlankLine()
    {
        $txtFile = new TextFileDataset(self::$fileName_BlankLine, self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_Remote_Unix()
    {
        $txtFile = new TextFileDataset(self::RemoteURL . basename(self::$fileName_Unix), self::$fieldNames,
            TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_Remote_Windows()
    {
        $txtFile = new TextFileDataset(self::RemoteURL . basename(self::$fileName_Windows), self::$fieldNames,
            TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    /**
     * fsockopen and fgets is buggy when read a Mac classic document (\r line ending)
     */
    function test_navigateTextIterator_Remote_MacClassic()
    {
        $txtFile = new TextFileDataset(self::RemoteURL . basename(self::$fileName_MacClassic), self::$fieldNames,
            TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    function test_navigateTextIterator_Remote_BlankLine()
    {
        $txtFile = new TextFileDataset(self::RemoteURL . basename(self::$fileName_BlankLine), self::$fieldNames,
            TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();

        $count = 0;
        foreach ($txtIterator as $sr) {
            $this->assertSingleRow($sr, ++$count);
        }

        $this->assertEquals($count, 2000);
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\NotFoundException
     */
    function test_fileNotFound()
    {
        $txtFile = new TextFileDataset("/tmp/xyz", self::$fieldNames, TextFileDataset::CSVFILE);
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\DatasetException
     */
    function test_remoteFileNotFound()
    {
        $txtFile = new TextFileDataset(self::RemoteURL . "notfound-test", self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();
    }

    /**
     * @expectedException \ByJG\AnyDataset\Exception\DatasetException
     */
    function test_serverNotFound()
    {
        $txtFile = new TextFileDataset("http://notfound-test/alalal", self::$fieldNames, TextFileDataset::CSVFILE);
        $txtIterator = $txtFile->getIterator();
    }

    /**
     *
     * @param SingleRow $sr
     */
    function assertSingleRow($sr, $count)
    {
        $this->assertEquals($sr->getField("field1"), $count);
        $this->assertEquals($sr->getField("field2"), "STRING$count");
        $this->assertEquals($sr->getField("field3"), "VALUE$count");
    }

    function assertRowCount($it, $qty)
    {
        $count = 0;
        foreach ($it as $sr)
            $count++;

        $this->assertEquals($qty, $count);
    }
}

?>