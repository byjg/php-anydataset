<?php

namespace Store;

use ByJG\AnyDataset\Factory;

require_once 'BasePdoTest.php';

class PdoPostgresTest extends BasePdoTest
{

    protected function createInstance()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('postgres://postgres:password@postgres-container/test');
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id INTEGER PRIMARY KEY auto_increment, Breed TEXT, Name TEXT, Age INTEGER)");
    }

    public function tearDown()
    {
        $this->dbDriver->execute('drop table Dogs;');
    }
}
