<?php
/**
 * User: jg
 * Date: 16/02/17
 * Time: 10:17
 */

namespace Store;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Factory;

class BasePdoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DbDriverInterface
     */
    protected $dbDriver;

    public function setUp()
    {
        $this->createInstance();
        $this->createDatabase();
        $this->populateData();
    }

    protected function createInstance()
    {
        throw new \Exception('Implement createInstance method');
    }

    protected function populateData()
    {
        //insert some data...
        $array = $this->allData();
        foreach ($array as $param) {
            $this->dbDriver->execute(
                "INSERT INTO Dogs (Breed, Name, Age) VALUES (:breed, :name, :age);",
                $param
            );
        }
    }

    public function tearDown()
    {
        unlink('/tmp/test.db');
    }

    protected function allData()
    {
        return [
            [
                'breed' => 'Mutt',
                'name' => 'Spyke',
                'age' => 8,
                'id' => 1
            ],
            [
                'breed' => 'Brazilian Terrier',
                'name' => 'Sandy',
                'age' => 3,
                'id' => 2
            ],
            [
                'breed' => 'Pinscher',
                'name' => 'Lola',
                'age' => 1,
                'id' => 3
            ]
        ];
    }

    public function testGetIterator()
    {
        $array = $this->allData();

        // Step 1
        $iterator = $this->dbDriver->getIterator('select * from Dogs');
        $this->assertEquals($array, $iterator->toArray());

        // Step 2
        $iterator = $this->dbDriver->getIterator('select * from Dogs');
        $i = 0;
        foreach ($iterator as $singleRow) {
            $this->assertEquals($array[$i++], $singleRow->toArray());
        }

        // Step 3
        $iterator = $this->dbDriver->getIterator('select * from Dogs');
        $i = 0;
        while ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $this->assertEquals($array[$i++], $singleRow->toArray());
        }

        // See --> http://php.net/manual/pt_BR/pdostatement.rowcount.php
        //$this->assertEquals(3, $iterator->count());
    }

    public function testExecuteAndGetId()
    {
        $idInserted = $this->dbDriver->executeAndGetId(
            "INSERT INTO Dogs (Breed, Name, Age) VALUES ('Cat', 'Doris', 7);"
        );

        $this->assertEquals(4, $idInserted);
    }

    public function testGetAllFields()
    {
        $allFields = $this->dbDriver->getAllFields('Dogs');

        $this->assertEquals(
            [
                'id',
                'breed',
                'name',
                'age'
            ],
            $allFields
        );
    }
}
