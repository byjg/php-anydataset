<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 *
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\Oci8Iterator;
use Xmlnuke\Core\Exception\DataBaseException;

class DBOci8Driver implements IDBDriver
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
			throw new DataBaseException($e['message']);
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
		list($query, $array) = XmlnukeProviderFactory::ParseSQL ( $this->_connectionManagement, $sql, $array );

		//$query = ForceUTF8\Encoding::toWin1252($query);

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

		$r = oci_commit($this->_conn);
		if (!r)
		{
			$e = oci_error($this->conn);
			throw new DataBaseException($e['message']);
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
