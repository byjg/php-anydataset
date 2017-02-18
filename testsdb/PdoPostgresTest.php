<?php

namespace Store;

use ByJG\AnyDataset\Factory;

require_once 'BasePdoTest.php';

class PdoPostgresTest extends BasePdoTest
{

    protected function createInstance()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('pgsql://postgres:password@postgres-container/test');
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id SERIAL PRIMARY KEY, Breed VARCHAR(50), Name VARCHAR(50), Age INTEGER)");
    }

    public function tearDown()
    {
        $this->dbDriver->execute('drop table Dogs;');
    }
}
