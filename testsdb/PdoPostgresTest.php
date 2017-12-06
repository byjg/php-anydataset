<?php

namespace TestsDb\AnyDataset;

use ByJG\AnyDataset\Factory;

require_once 'BasePdo.php';

class PdoPostgresTest extends BasePdo
{

    protected function createInstance()
    {
        $password = getenv('PSQL_PASSWORD');
        if (empty($password)) {
            $password = 'password';
        }
        if ($password == '.') {
            $password = "";
        }

        $this->dbDriver = Factory::getDbRelationalInstance("pgsql://postgres:$password@postgres-container");
        $exists = $this->dbDriver->getScalar('select count(1) from pg_catalog.pg_database where datname = \'test\'');
        if ($exists == 0) {
            $this->dbDriver->execute('CREATE DATABASE test');
        }
        $this->dbDriver = Factory::getDbRelationalInstance("pgsql://postgres:$password@postgres-container/test");
    }

    protected function createDatabase()
    {
        //create the database
        $this->dbDriver->execute("CREATE TABLE Dogs (Id SERIAL PRIMARY KEY, Breed VARCHAR(50), Name VARCHAR(50), Age INTEGER)");
    }

    public function deleteDatabase()
    {
        $this->dbDriver->execute('drop table Dogs;');
    }
}
