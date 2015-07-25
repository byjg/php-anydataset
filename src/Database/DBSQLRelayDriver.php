<?php


namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Database\ConnectionManagement;
use ByJG\AnyDataset\Database\DBDriverInterface;
use ByJG\AnyDataset\Database\SQLHelper;
use ByJG\AnyDataset\Database\SQLBind;
use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\AnyDataset\Repository\SQLRelayIterator;

class DBSQLRelayDriver implements DBDriverInterface
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/** Used for SQL Relay connections **/
	protected $_conn;

	protected $_transaction = false;

	public function __construct($connMngt)
	{
		$this->_connectionManagement = $connMngt;

		$this->_conn = sqlrcon_alloc (
				$this->_connectionManagement->getServer(),
				$this->_connectionManagement->getPort(),
				$this->_connectionManagement->getExtraParam("unixsocket"),
				$this->_connectionManagement->getUsername(),
				$this->_connectionManagement->getPassword(),
				0,
				1
			);

		sqlrcon_autoCommitOn($this->_conn);
	}

	public function __destruct()
	{
		if (! is_null ( $this->_conn ))
		{
			sqlrcon_free ( $this->_conn );
		}
	}

	protected function getSQLRelayCursor($sql, $array = null)
	{
		$cur = sqlrcur_alloc ( $this->_conn );
		$success = true;

		if ($array)
		{
			list($sql, $array) = SQLBind::parseSQL ( $this->_connectionManagement, $sql, $array );

			sqlrcur_prepareQuery ( $cur, $sql );
			$bindCount = 1;
			foreach ( $array as $key => $value )
			{
				$field = strval ( $bindCount ++ );
				sqlrcur_inputBind ( $cur, $field, $value );
			}
			$success = sqlrcur_executeQuery ( $cur );
			sqlrcon_endSession ( $this->_conn );
		}
		else
		{
			$success = sqlrcur_sendQuery ( $cur, $sql );
			sqlrcon_endSession ( $this->_conn );
		}
		if (!$success)
		{
			throw new DatasetException(sqlrcur_errorMessage($cur));
		}

		sqlrcur_lowerCaseColumnNames($cur);
		return $cur;
	}

	public function getIterator($sql, $array = null)
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		$it = new SQLRelayIterator ( $cur );
		return $it;
	}

	public function getScalar($sql, $array = null)
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		$scalar = sqlrcur_getField($cur,0,0);
		sqlrcur_free($cur);

		return $scalar;
	}

	public function getAllFields($tablename)
	{
		$cur=sqlrcur_alloc($this->_conn);

		$success = sqlrcur_sendQuery($cur, SQLHelper::createSafeSQL("select * from :table", array(":table"=>$tablename)));
		sqlrcon_endSession($con);

		if (!$success)
		{
			throw new DatasetException(sqlrcur_errorMessage($cur));
		}

		$fields = [];
		$colCount = sqlrcur_colCount($cur);
		for ($col=0; $col<$colCount; $col++)
		{
			$fields[] = strtolower(sqlrcur_getColumnName($cur, $col));
		}

		sqlrcur_free($cur);

		return $fields;
	}

	public function beginTransaction()
	{
		$this->_transaction = true;
		sqlrcon_autoCommitOff($this->_conn);
	}

	public function commitTransaction()
	{
		if ($this->_transaction)
		{
			$this->_transaction = false;

			$ret = sqlrcon_commit($this->_conn);
			if ($ret === 0)
			{
				throw new DataBaseException('Commit failed');
			}
			else if ($ret === -1)
			{
				throw new DataBaseException('An error occurred. Commit failed');
			}

			sqlrcon_autoCommitOn($this->_conn);
		}
	}

	public function rollbackTransaction()
	{
		if ($this->_transaction)
		{
			$this->_transaction = false;

			$ret = sqlrcon_rollback($this->_conn);
			if ($ret === 0)
			{
				throw new DatabaseException('Commit failed');
			}
			else if ($ret === -1)
			{
				throw new DatabaseException('An error occurred. Commit failed');
			}

			sqlrcon_autoCommitOn($this->_conn);
		}
	}

	public function executeSql($sql, $array = null)
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		sqlrcur_free($cur);
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
		throw new NotAvailableException('Method not implemented for SQL Relay Driver');
	}

	public function setAttribute($name, $value)
	{
		throw new NotAvailableException('Method not implemented for SQL Relay Driver');
	}

}
