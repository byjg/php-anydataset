<?php

namespace Store;

use ByJG\AnyDataset\Factory;

require_once 'BasePdo.php';

class PdoSqliteTest extends BasePdo
{

    protected function createInstance()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('sqlite:///tmp/test.db');
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id INTEGER PRIMARY KEY, Breed VARCHAR(50), Name VARCHAR(50), Age INTEGER)");
    }

    public function tearDown()
    {
        unlink('/tmp/test.db');
    }

    public function testGetAllFields()
    {
        // Ignore this test
    }
}
