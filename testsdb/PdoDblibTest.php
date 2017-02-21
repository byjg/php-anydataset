<?php

namespace Store;

use ByJG\AnyDataset\Factory;

require_once 'BasePdo.php';

class PdoDblibTest extends BasePdo
{

    protected function createInstance()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('dblib://sa:Pa$$word!@mssql-container/tempdb');
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id INT NOT NULL IDENTITY(1,1) PRIMARY KEY, Breed VARCHAR(50), Name VARCHAR(50), Age INTEGER)");
    }

    public function tearDown()
    {
        $this->dbDriver->execute('drop table Dogs;');
    }
}
