<?php

namespace ByJG\AnyDataset\Store;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Dataset\SqlRelayIterator;
use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\AnyDataset\Store\Helpers\SqlBind;
use ByJG\AnyDataset\Store\Helpers\SqlHelper;
use ByJG\Util\Uri;

class DbSqlRelayDriver implements DbDriverInterface
{

    /**
     * Enter description here...
     *
     * @var Uri
     */
    protected $connectionUri;

    /** Used for SQL Relay connections * */
    protected $conn;
    protected $transaction = false;

    public function __construct(Uri $connUri)
    {
        $this->connectionUri = $connUri;

        $this->conn = sqlrcon_alloc(
            $this->connectionUri->getScheme(),
            $this->connectionUri->getPort(),
            $this->connectionUri->getQueryPart("unixsocket"),
            $this->connectionUri->getUsername(),
            $this->connectionUri->getPassword(),
            0,
            1
        );

        sqlrcon_autoCommitOn($this->conn);
    }

    public function __destruct()
    {
        if (!is_null($this->conn)) {
            sqlrcon_free($this->conn);
        }
    }

    protected function getSQLRelayCursor($sql, $array = null)
    {
        $cur = sqlrcur_alloc($this->conn);

        if ($array) {
            list($sql, $array) = SqlBind::parseSQL($this->connectionUri, $sql, $array);

            sqlrcur_prepareQuery($cur, $sql);
            $bindCount = 1;
            foreach ($array as $key => $value) {
                $field = strval($bindCount ++);
                sqlrcur_inputBind($cur, $field, $value);
            }
            $success = sqlrcur_executeQuery($cur);
            sqlrcon_endSession($this->conn);
        } else {
            $success = sqlrcur_sendQuery($cur, $sql);
            sqlrcon_endSession($this->conn);
        }
        if (!$success) {
            throw new DatasetException(sqlrcur_errorMessage($cur));
        }

        sqlrcur_lowerCaseColumnNames($cur);
        return $cur;
    }

    public function getIterator($sql, $params = null)
    {
        $cur = $this->getSQLRelayCursor($sql, $params);
        $iterator = new SqlRelayIterator($cur);
        return $iterator;
    }

    public function getScalar($sql, $array = null)
    {
        $cur = $this->getSQLRelayCursor($sql, $array);
        $scalar = sqlrcur_getField($cur, 0, 0);
        sqlrcur_free($cur);

        return $scalar;
    }

    public function getAllFields($tablename)
    {
        $cur = sqlrcur_alloc($this->conn);

        $success = sqlrcur_sendQuery(
            $cur,
            SqlHelper::createSafeSQL("select * from :table", array(":table" => $tablename))
        );
        sqlrcon_endSession($cur);

        if (!$success) {
            throw new DatasetException(sqlrcur_errorMessage($cur));
        }

        $fields = [];
        $colCount = sqlrcur_colCount($cur);
        for ($col = 0; $col < $colCount; $col++) {
            $fields[] = strtolower(sqlrcur_getColumnName($cur, $col));
        }

        sqlrcur_free($cur);

        return $fields;
    }

    public function beginTransaction()
    {
        $this->transaction = true;
        sqlrcon_autoCommitOff($this->conn);
    }

    public function commitTransaction()
    {
        if ($this->transaction) {
            $this->transaction = false;

            $ret = sqlrcon_commit($this->conn);
            if ($ret === 0) {
                throw new DatabaseException('Commit failed');
            } elseif ($ret === -1) {
                throw new DatabaseException('An error occurred. Commit failed');
            }

            sqlrcon_autoCommitOn($this->conn);
        }
    }

    public function rollbackTransaction()
    {
        if ($this->transaction) {
            $this->transaction = false;

            $ret = sqlrcon_rollback($this->conn);
            if ($ret === 0) {
                throw new DatabaseException('Commit failed');
            } elseif ($ret === -1) {
                throw new DatabaseException('An error occurred. Commit failed');
            }

            sqlrcon_autoCommitOn($this->conn);
        }
    }

    public function execute($sql, $array = null)
    {
        $cur = $this->getSQLRelayCursor($sql, $array);
        sqlrcur_free($cur);
        return true;
    }

    /**
     *
     * @return bool
     */
    public function getDbConnection()
    {
        return $this->conn;
    }

    public function getAttribute($name)
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }

    public function setAttribute($name, $value)
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }

    public function executeAndGetId($sql, $array = null)
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }

    public function getDbHelper()
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->connectionUri;
    }

    public function isSupportMultRowset()
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }

    public function setSupportMultRowset($multipleRowSet)
    {
        throw new NotImplementedException('Method not implemented for SQL Relay Driver');
    }
}
