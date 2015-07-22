<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/

/**
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Repository;

use InvalidArgumentException;
use ByJG\AnyDataset\Database\ConnectionManagement;
use ByJG\AnyDataset\Database\INoSQLDriver;
use ByJG\AnyDataset\Database\MongoDBDriver;

class NoSQLDataSet implements INoSQLDriver
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/**
	 *
	 * @var INoSQLDriver
	 */
	protected $_dbDriver = null;

    /**
     *
     * @param type $dbname
     * @param type $collection
     * @throws InvalidArgumentException
     */
	public function __construct($dbname, $collection)
	{
		$this->_connectionManagement = new ConnectionManagement ( $dbname );

		if ($this->_connectionManagement->getDriver() == "mongodb")
			$this->_dbDriver = new MongoDBDriver($this->_connectionManagement, $collection);
		else
			throw new InvalidArgumentException("There is no '{$this->_connectionManagement->getDriver()}' NoSQL database");
	}

	public function getDbType()
	{
		return $this->_connectionManagement->getDbType();
	}

	public function getDbConnectionString()
	{
		return $this->_connectionManagement->getDbConnectionString ();
	}

	public function testConnection()
	{
		return true;
	}



	/**
	 *
	 * @return mixed
	 */
	public function getCollection()
	{
		return $this->_dbDriver->getCollection();
	}

	/**
	 *
	 * @param mixed $filter
	 * @return IIterator $filter
	 */
	public function getIterator($filter = null, $fields = null)
	{
		return $this->_dbDriver->getIterator($filter, $fields);
	}

	/**
	 *
	 * @param void $document
	 */
	public function insert($document)
	{
		$this->_dbDriver->insert($document);
	}

	/**
	 *
	 * @param mixed $collection
	 * @return bool
	 */
	public function setCollection($collection)
	{
		return $this->_dbDriver->setCollection($collection);
	}

	/**
	 *
	 * @param mixed $document
	 * @param mixed $filter
	 * @return bool
	 */
	public function update($document, $filter = null, $options = null)
	{
		return $this->_dbDriver->update($document, $filter, $options);
	}


	/**
	 *@access public
	 *@return string
	 */
	public function getDBConnection()
	{
		return $this->_dbDriver->getDbConnection();
	}


	/*
	public function setDriverAttribute($name, $value)
	{
		return $this->_dbDriver->setAttribute($name, $value);
	}

	public function getDriverAttribute($name)
	{
		return $this->_dbDriver->getAttribute($name);
	}
	 *
	 */

}

?>
