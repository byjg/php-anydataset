<?php

namespace TestsDb\AnyDataset;

use ByJG\AnyDataset\Factory;

require_once 'BasePdo.php';

class PdoMySqlest extends BasePdo
{

    protected function createInstance()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('mysql://root:password@mysql-container');
        $this->dbDriver->execute('CREATE DATABASE IF NOT EXISTS test');
        $this->dbDriver = Factory::getDbRelationalInstance('mysql://root:password@mysql-container/test');
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id INTEGER PRIMARY KEY auto_increment, Breed VARCHAR(50), Name VARCHAR(50), Age INTEGER)");
    }

    public function deleteDatabase()
    {
        $this->dbDriver->execute('drop table Dogs;');
    }
}
