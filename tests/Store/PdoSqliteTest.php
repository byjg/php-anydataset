<?php

namespace Tests\AnyDataset\Store;

use ByJG\AnyDataset\Factory;
use ByJG\AnyDataset\Store\Helpers\DbSqliteFunctions;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class PdoSqliteTest extends TestCase
{
    /**
     * @var \ByJG\AnyDataset\DbDriverInterface
     */
    protected $dbDriver;

    public function setUp()
    {
        $this->dbDriver = Factory::getDbRelationalInstance('sqlite:///tmp/test.db');

        $this->dbDriver->execute(
            'create table users (
            id integer primary key  autoincrement, 
            name varchar(45), 
            createdate datetime);'
        );
        $this->dbDriver->execute("insert into users (name, createdate) values ('John Doe', '2017-01-02')");
        $this->dbDriver->execute("insert into users (name, createdate) values ('Jane Doe', '2017-01-04')");
        $this->dbDriver->execute("insert into users (name, createdate) values ('JG', '1974-01-26')");


        $this->dbDriver->execute(
            'create table info (
            id integer primary key  autoincrement,
            iduser INTEGER,
            property varchar(45));'
        );
        $this->dbDriver->execute("insert into info (iduser, property) values (1, 'xxx')");
        $this->dbDriver->execute("insert into info (iduser, property) values (1, 'ggg')");
        $this->dbDriver->execute("insert into info (iduser, property) values (3, 'bbb')");
    }

    public function tearDown()
    {
        unlink('/tmp/test.db');
    }

    public function testGetIterator()
    {
        $iterator = $this->dbDriver->getIterator('select * from info');
        $expected =
            [
                [ 'id'=> 1, 'iduser' => 1, 'property' => 'xxx'],
                [ 'id'=> 2, 'iduser' => 1, 'property' => 'ggg'],
                [ 'id'=> 3, 'iduser' => 3, 'property' => 'bbb'],
            ];

        // To Array
        $this->assertEquals(
            $expected,
            $iterator->toArray()
        );

        // While
        $iterator = $this->dbDriver->getIterator('select * from info');
        $i = 0;
        while ($iterator->hasNext()) {
            $row = $iterator->moveNext();
            $this->assertEquals($expected[$i++], $row->toArray());
        }

        // Foreach
        $iterator = $this->dbDriver->getIterator('select * from info');
        $i = 0;
        foreach ($iterator as $row) {
            $this->assertEquals($expected[$i++], $row->toArray());
        }
    }

    public function testGetIteratorFilter()
    {
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 1]);
        $expected =
            [
                [ 'id'=> 1, 'iduser' => 1, 'property' => 'xxx'],
                [ 'id'=> 2, 'iduser' => 1, 'property' => 'ggg'],
            ];

        // To Array
        $this->assertEquals(
            $expected,
            $iterator->toArray()
        );

        // While
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 1]);
        $i = 0;
        while ($iterator->hasNext()) {
            $row = $iterator->moveNext();
            $this->assertEquals($expected[$i++], $row->toArray());
        }

        // Foreach
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 1]);
        $i = 0;
        foreach ($iterator as $row) {
            $this->assertEquals($expected[$i++], $row->toArray());
        }
    }

    public function testGetIteratorNotFound()
    {
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 5]);

        // To Array
        $this->assertEquals(
            [],
            $iterator->toArray()
        );

        // While
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 5]);
        $this->assertFalse($iterator->hasNext());

        // Foreach
        $iterator = $this->dbDriver->getIterator('select * from info where iduser = :id', ['id' => 5]);
        $i = 0;
        foreach ($iterator as $row) {
            $i++;
        }
        $this->assertEquals(0, $i);
    }

    public function testGetScalar()
    {
        $count1 = $this->dbDriver->getScalar('select count(*) from info');
        $this->assertEquals(3, $count1);

        $count2 = $this->dbDriver->getScalar('select count(*) from info where iduser = :id', ['id' => 1]);
        $this->assertEquals(2, $count2);

        $count3 = $this->dbDriver->getScalar('select count(*) from info where iduser = :id', ['id' => 5]);
        $this->assertEquals(0, $count3);
    }

    // public function testGetAllFields()
    // {
    //     $this->assertEquals(
    //         ['id', 'iduser', 'info'],
    //         $this->dbDriver->getAllFields('info')
    //     );
    // }

    public function testExecute()
    {
        $this->dbDriver->execute("insert into users (name, createdate) values ('Another', '2017-05-11')");
        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another']);

        $this->assertEquals(
            [
                ['id' => 4, 'name' => 'Another', 'createdate' => '2017-05-11'],
            ],
            $iterator->toArray()
        );
    }

    public function testExecuteAndGetId()
    {
        $newId = $this->dbDriver->executeAndGetId("insert into users (name, createdate) values ('Another', '2017-05-11')");

        $this->assertEquals(4, $newId);
        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another']);

        $this->assertEquals(
            [
                ['id' => 4, 'name' => 'Another', 'createdate' => '2017-05-11'],
            ],
            $iterator->toArray()
        );
    }

    public function testGetDbHelper()
    {
        $helper = $this->dbDriver->getDbHelper();
        $this->assertInstanceOf(DbSqliteFunctions::class, $helper);
    }

    public function testTransaction()
    {
        $this->dbDriver->beginTransaction();
        $newId = $this->dbDriver->executeAndGetId("insert into users (name, createdate) values ('Another', '2017-05-11')");
        $this->assertEquals(4, $newId);
        $this->dbDriver->commitTransaction();

        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another']);

        $this->assertEquals(
            [
                ['id' => 4, 'name' => 'Another', 'createdate' => '2017-05-11'],
            ],
            $iterator->toArray()
        );
    }

    public function testTransaction2()
    {
        $this->dbDriver->beginTransaction();
        $newId = $this->dbDriver->executeAndGetId("insert into users (name, createdate) values ('Another', '2017-05-11')");
        $this->assertEquals(4, $newId);
        $this->dbDriver->rollbackTransaction();

        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another']);
        $this->assertFalse($iterator->hasNext());
    }


    public function testTransactionTwoContext()
    {
        // Context 1
        $this->dbDriver->beginTransaction();
        $newId = $this->dbDriver->executeAndGetId("insert into users (name, createdate) values ('Another', '2017-05-11')");
        $this->assertEquals(4, $newId);
        $this->dbDriver->rollbackTransaction();

        // Context 2
        $context2 = Factory::getDbRelationalInstance('sqlite:///tmp/test.db');
        $context2->beginTransaction();
        $newId = $context2->executeAndGetId("insert into users (name, createdate) values ('Another2', '2017-04-11')");
        $this->assertEquals(4, $newId);
        $context2->commitTransaction();

        // Check values
        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another']);
        $this->assertFalse($iterator->hasNext());

        $iterator = $this->dbDriver->getIterator('select * from users where name = [[name]]', ['name' => 'Another2']);
        $this->assertEquals(
            [
                ['id' => 4, 'name' => 'Another2', 'createdate' => '2017-04-11'],
            ],
            $iterator->toArray()
        );
    }

    public function testGetDbConnection()
    {
        $connection = $this->dbDriver->getDbConnection();
        $this->assertInstanceOf(\PDO::class, $connection);
    }

    public function testGetUri()
    {
        $uri = $this->dbDriver->getUri();
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('sqlite:///tmp/test.db', $uri->__toString());
    }

    // public function testSetAttribute()
    // {
    //     $this->assertNotEquals(\PDO::CASE_UPPER, $this->dbDriver->getAttribute(\PDO::ATTR_CASE));
    //     $this->dbDriver->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
    //     $this->assertEquals(\PDO::CASE_UPPER, $this->dbDriver->getAttribute(\PDO::ATTR_CASE));
    // }

    public function testisSupportMultRowset()
    {
        $this->assertFalse($this->dbDriver->isSupportMultRowset());
    }
}
