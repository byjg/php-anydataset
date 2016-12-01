<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\AnyDataset\Repository\Oci8Iterator;

class DbOci8Driver implements DbDriverInterface
{

    /**
     * Enter description here...
     *
     * @var ConnectionManagement
     */
    protected $connectionManagement;

    /** Used for OCI8 connections * */
    protected $conn;
    protected $transaction = OCI_COMMIT_ON_SUCCESS;

    /**
     * Ex.
     *
     *    oci8://username:password@host:1521/servicename?protocol=TCP&codepage=WE8MSWIN1252
     *
     * @param ConnectionManagement $connMngt
     * @throws DatabaseException
     */
    public function __construct($connMngt)
    {
        $this->connectionManagement = $connMngt;

        $codePage = $this->connectionManagement->getExtraParam("codepage");
        $codePage = ($codePage == "") ? 'UTF8' : $codePage;

        $tns = DbOci8Driver::getTnsString($connMngt);

        $this->conn = oci_connect(
            $this->connectionManagement->getUsername(),
            $this->connectionManagement->getPassword(),
            $tns,
            $codePage
        );

        if (!$this->conn) {
            $error = oci_error();
            throw new DatabaseException($error['message']);
        }
    }

    /**
     *
     * @param ConnectionManagement $connMngt
     * @return string
     */
    public static function getTnsString($connMngt)
    {
        $protocol = $connMngt->getExtraParam("protocol");
        $protocol = ($protocol == "") ? 'TCP' : $protocol;

        $port = $connMngt->getPort();
        $port = ($port == "") ? 1521 : $port;

        $svcName = $connMngt->getDatabase();

        $host = $connMngt->getServer();

        $tns = "(DESCRIPTION = " .
            "	(ADDRESS = (PROTOCOL = $protocol)(HOST = $host)(PORT = $port)) " .
            "		(CONNECT_DATA = (SERVICE_NAME = $svcName)) " .
            ")";

        return $tns;
    }

    public function __destruct()
    {
        $this->conn = null;
    }

    protected function getOci8Cursor($sql, $array = null)
    {
        list($query, $array) = SqlBind::parseSQL($this->connectionManagement, $sql, $array);

        // Prepare the statement
        $stid = oci_parse($this->conn, $query);
        if (!$stid) {
            $error = oci_error($this->conn);
            throw new DatabaseException($error['message']);
        }

        // Bind the parameters
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                oci_bind_by_name($stid, ":$key", $value);
            }
        }

        // Perform the logic of the query
        $result = oci_execute($stid, $this->transaction);

        // Check if is OK;
        if (!$result) {
            $error = oci_error($stid);
            throw new DatabaseException($error['message']);
        }

        return $stid;
    }

    public function getIterator($sql, $array = null)
    {
        $cur = $this->getOci8Cursor($sql, $array);
        $iterator = new Oci8Iterator($cur);
        return $iterator;
    }

    public function getScalar($sql, $array = null)
    {
        $cur = $this->getOci8Cursor($sql, $array);

        $row = oci_fetch_array($cur, OCI_RETURN_NULLS);
        if ($row) {
            $scalar = $row[0];
        } else {
            $scalar = null;
        }

        oci_free_cursor($cur);

        return $scalar;
    }

    public function getAllFields($tablename)
    {
        $cur = $this->getOci8Cursor(SqlHelper::createSafeSQL("select * from :table", array(':table' => $tablename)));

        $ncols = oci_num_fields($cur);

        $fields = array();
        for ($i = 1; $i <= $ncols; $i++) {
            $fields[] = strtolower(oci_field_name($cur, $i));
        }

        oci_free_statement($cur);

        return $fields;
    }

    public function beginTransaction()
    {
        $this->transaction = OCI_NO_AUTO_COMMIT;
    }

    public function commitTransaction()
    {
        if ($this->transaction == OCI_COMMIT_ON_SUCCESS) {
            throw new DatabaseException('No transaction for commit');
        }

        $this->transaction = OCI_COMMIT_ON_SUCCESS;

        $result = oci_commit($this->conn);
        if (!$result) {
            $error = oci_error($this->conn);
            throw new DatabaseException($error['message']);
        }
    }

    public function rollbackTransaction()
    {
        if ($this->transaction == OCI_COMMIT_ON_SUCCESS) {
            throw new DatabaseException('No transaction for rollback');
        }

        $this->transaction = OCI_COMMIT_ON_SUCCESS;

        oci_rollback($this->conn);
    }

    public function executeSql($sql, $array = null)
    {
        $cur = $this->getOci8Cursor($sql, $array);
        oci_free_cursor($cur);
        return true;
    }

    /**
     *
     * @return resource
     */
    public function getDbConnection()
    {
        return $this->conn;
    }

    public function getAttribute($name)
    {
        throw new \Exception('Method not implemented for OCI Driver');
    }

    public function setAttribute($name, $value)
    {
        throw new \Exception('Method not implemented for OCI Driver');
    }
}
