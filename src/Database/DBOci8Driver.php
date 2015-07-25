<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\Oci8Iterator;
use ByJG\AnyDataset\Exception\DatabaseException;

class DBOci8Driver implements DBDriverInterface
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/** Used for OCI8 connections **/
	protected $_conn;

	protected $_transaction = OCI_COMMIT_ON_SUCCESS;

	/**
	 * Ex.
	 *
	 *	oci8://username:password@host:1521/servicename?protocol=TCP&codepage=WE8MSWIN1252
	 *
	 * @param ConnectionManagement $connMngt
	 */
	public function __construct($connMngt)
	{
		$this->_connectionManagement = $connMngt;

		$codePage = $this->_connectionManagement->getExtraParam("codepage");
		$codePage = ($codePage == "") ? 'UTF8' : $codePage;

		$tns = DBOci8Driver::getTnsString($connMngt);

		$this->_conn = oci_connect(
				$this->_connectionManagement->getUsername(),
				$this->_connectionManagement->getPassword(),
				$tns,
				$codePage
		);

		if (!$this->_conn) {
			$e = oci_error();
			throw new DatabaseException($e['message']);
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

		$tns =
			"(DESCRIPTION = " .
			"	(ADDRESS = (PROTOCOL = $protocol)(HOST = $host)(PORT = $port)) " .
			"		(CONNECT_DATA = (SERVICE_NAME = $svcName)) " .
			")";

		return $tns;
	}

	public function __destruct()
	{
		$this->_conn = null;
	}

	protected function getOci8Cursor($sql, $array = null)
	{
		list($query, $array) = SQLBind::parseSQL ( $this->_connectionManagement, $sql, $array );

		// Prepare the statement
		$stid = oci_parse($this->_conn, $query);
		if (!$stid) {
			$e = oci_error($this->_conn);
			throw new DatabaseException($e['message']);
		}

		// Bind the parameters
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				oci_bind_by_name($stid, ":$key", $value);
			}
		}

		// Perform the logic of the query
		$r = oci_execute($stid, $this->_transaction);

		// Check if is OK;
		if (!$r) {
		    $e = oci_error($stid);
			throw new DatabaseException($e['message']);
		}

		return $stid;
	}

	public function getIterator($sql, $array = null)
	{
		$cur = $this->getOci8Cursor($sql, $array);
		$it = new Oci8Iterator ( $cur );
		return $it;
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
		$cur = $this->getOci8Cursor(SQLHelper::createSafeSQL("select * from :table", array(':table' => $tablename)));

		$ncols = oci_num_fields($cur);

		$fields = array ();
		for ($i = 1; $i <= $ncols; $i++)
		{
			$fields[] = strtolower(oci_field_name($cur, $i));
		}

		oci_free_statement($cur);

		return $fields;
	}

	public function beginTransaction()
	{
		$this->_transaction = OCI_NO_AUTO_COMMIT;
	}

	public function commitTransaction()
	{
		if ($this->_transaction == OCI_COMMIT_ON_SUCCESS) {
            throw new DataBaseException('No transaction for commit');
        }

        $this->_transaction = OCI_COMMIT_ON_SUCCESS;

		$result = oci_commit($this->_conn);
		if (!$result)
		{
			$error = oci_error($this->conn);
			throw new DataBaseException($error['message']);
		}
	}

	public function rollbackTransaction()
	{
		if ($this->_transaction == OCI_COMMIT_ON_SUCCESS) {
            throw new DataBaseException('No transaction for rollback');
        }

        $this->_transaction = OCI_COMMIT_ON_SUCCESS;

		oci_rollback($this->_conn);
	}

	public function executeSql($sql, $array = null)
	{
		$cur = $this->getOci8Cursor($sql, $array);
		oci_free_cursor($cur);
		return true;
	}

	/**
	 *
	 * @return handle
	 */
	public function getDbConnection()
	{
		return $this->_conn;
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
