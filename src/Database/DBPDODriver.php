<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBIterator;
use PDO;
use PDOStatement;

class DBPDODriver implements DBDriverInterface
{	
	/**
	 * @var PDO
	 */
	protected $_db = null;
	
	/**
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	public function __construct(ConnectionManagement $connMngt, $strcnn, $preOptions, $postOptions)
	{	
		$this->_connectionManagement = $connMngt;

		if (is_null($strcnn))
		{
			if ($this->_connectionManagement->getFilePath() != "")
			{
				$strcnn = $this->_connectionManagement->getDriver() . ":" . $this->_connectionManagement->getFilePath();
			}
			else
			{
				$strcnn = $this->_connectionManagement->getDriver() . ":dbname=" . $this->_connectionManagement->getDatabase();
				if ($this->_connectionManagement->getExtraParam("unixsocket") != "")
					$strcnn .= ";unix_socket=" . $this->_connectionManagement->getExtraParam("unixsocket");
				else
				{
					$strcnn .= ";host=" . $this->_connectionManagement->getServer();
					if ($this->_connectionManagement->getPort() != "")
						$strcnn .= ";port=" . $this->_connectionManagement->getPort();
				}
			}
		}

		// Create Connection
		$this->_db = new PDO ( $strcnn, $this->_connectionManagement->getUsername (), $this->_connectionManagement->getPassword (), (array)$preOptions );
		$this->_connectionManagement->setDriver($this->_db->getAttribute(PDO::ATTR_DRIVER_NAME));

		// Set Specific Attributes
		$this->_db->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->_db->setAttribute ( PDO::ATTR_CASE, PDO::CASE_LOWER );

		foreach ((array)$postOptions as $key=>$value)
		{
			$this->_db->setAttribute ( $key, $value );
		}
	}

	public static function factory(ConnectionManagement $connMngt)
	{
		$class = '\ByJG\AnyDataset\Database\Pdo' . ucfirst($connMngt->getDriver());

		if (!class_exists($class, true))
		{
			return new DBPDODriver($connMngt, null, null, null);
		}
		else
		{
			return new $class($connMngt);
		}
	}
	
	public function __destruct() 
	{
		$this->_db = null;
	}
	
	/**
	 *
	 * @param string $sql
	 * @param array $array
	 * @return PDOStatement
	 */
	protected function getDBStatement($sql, $array = null)
	{
		if ($array)
		{
			list($sql, $array) = SQLBind::parseSQL ( $this->_connectionManagement, $sql, $array );
			$stmt = $this->_db->prepare ( $sql );
			foreach ( $array as $key => $value )
			{
				$stmt->bindValue ( ":" . SQLBind::KeyAdj ( $key ), $value );
			}
		}
		else
			$stmt = $this->_db->prepare ( $sql );

		return $stmt;
	}
	
	public function getIterator($sql, $array = null)
	{
		$stmt = $this->getDBStatement($sql, $array);
		$stmt->execute();
		$it = new DBIterator ( $stmt );
		return $it;
	}
	
	public function getScalar($sql, $array = null)
	{
		$stmt = $this->getDBStatement($sql, $array);
		$stmt->execute();

		$scalar = $stmt->fetchColumn();

        $stmt->closeCursor();

		return $scalar;		
	}
	
	public function getAllFields($tablename) 
	{
		$fields = array ();
		$rs = $this->_db->query ( SQLHelper::createSafeSQL("select * from :table where 0=1", array(":table" => $tablename)) );
		$fieldLength = $rs->columnCount ();
		for($i = 0; $i < $fieldLength; $i++) 
		{
			$fld = $rs->getColumnMeta ( $i );
			$fields [] = strtolower ( $fld ["name"] );
		}
		return $fields;
	}
	
	public function beginTransaction()
	{
		$this->_db->beginTransaction();
	}
	
	public function commitTransaction()
	{
		$this->_db->commit();
	}
	
	public function rollbackTransaction()
	{
		$this->_db->rollBack();
	}
	
	public function executeSql($sql, $array = null) 
	{
		$stmt = $this->getDBStatement($sql, $array);
		$result = $stmt->execute ();
		return $result;
	}
	
	/**
	 * 
	 * @return PDO
	 */
	public function getDbConnection()
	{
		return $this->_db;
	}

	public function getAttribute($name)
	{
		$this->_db->getAttribute($name);
	}

	public function setAttribute($name, $value)
	{
		$this->_db->setAttribute ( $name, $value );
	}

}
