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

use Exception;
use PDO;
use PDOStatement;
use ByJG\AnyDataset\Repository\DBIterator;

class DBPDODriver implements IDBDriver
{	
	/**
	 * @var PDO
	 */
	protected $_db = null;
	
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	public function __construct(ConnectionManagement $connMngt, $strcnn, $preOptions, $postOptions)
	{	
		$this->_connectionManagement = $connMngt;

		if ($strcnn == null)
		{
			if ($this->_connectionManagement->getFilePath() != "")
				$strcnn = $this->_connectionManagement->getDriver () . ":" . $this->_connectionManagement->getFilePath ();
			else
			{
				$strcnn = $this->_connectionManagement->getDriver () . ":dbname=" . $this->_connectionManagement->getDatabase ();
				if ($this->_connectionManagement->getExtraParam("unixsocket") != "")
					$strcnn .= ";unix_socket=" . $this->_connectionManagement->getExtraParam("unixsocket");
				else
				{
					$strcnn .= ";host=" . $this->_connectionManagement->getServer ();
					if ($this->_connectionManagement->getPort() != "")
						$strcnn .= ";port=" . $this->_connectionManagement->getPort ();
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
			list($sql, $array) = XmlnukeProviderFactory::ParseSQL ( $this->_connectionManagement, $sql, $array );
			$stmt = $this->_db->prepare ( $sql );
			foreach ( $array as $key => $value )
			{
				$stmt->bindValue ( ":" . XmlnukeProviderFactory::KeyAdj ( $key ), $value );
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
