<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use ByJG\AnyDataset\Repository\SingleRow;
use InvalidArgumentException;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Core\Exception\DataBaseException;
use ByJG\AnyDataset\Exception\NotFoundException;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

/**
 * @package xmlnuke
 */

/**
 * Enter description here...
 *
 */
class ConnectionManagement
{
	protected $_dbtype;
	public function setDbType($value)
	{
	    $this->_dbtype = $value;
	}

	public function getDbType()
	{
	    return $this->_dbtype;
	}

	protected $_dbconnectionstring;
	public function setDbConnectionString($value)
	{
		$this->_dbconnectionstring = $value;
	}
	public function getDbConnectionString()
	{
		return $this->_dbconnectionstring;
	}

	protected $_driver;
	public function setDriver($value)
	{
		$this->_driver = $value;
	}
	public function getDriver()
	{
		return $this->_driver;
	}

	protected $_username;
	public function setUsername($value)
	{
		$this->_username = $value;
	}
	public function getUsername()
	{
		return $this->_username;
	}

	protected $_password;
	public function setPassword($value)
	{
		$this->_password = $value;
	}
	public function getPassword()
	{
		return $this->_password;
	}

	protected $_server;
	public function setServer($value)
	{
		$this->_server = $value;
	}
	public function getServer()
	{
		return $this->_server;
	}

	protected $_port = "";
	public function setPort($value)
	{
		$this->_port = $value;
	}
	public function getPort()
	{
		return $this->_port;
	}

	protected $_database;
	public function setDatabase($value)
	{
		$this->_database = $value;
	}
	public function getDatabase()
	{
		return $this->_database;
	}

	protected $_extraParam = array();
	public function addExtraParam($key, $value)
	{
		$this->_extraParam[$key] = $value;
	}
	public function getExtraParam($key)
	{
		if (array_key_exists($key, $this->_extraParam))
			return $this->_extraParam[$key];
		else
			return "";
	}

	protected $_file;
	public function setFilePath($value)
	{
		$this->_file = $value;
	}
	public function getFilePath()
	{
		return $this->_file;
	}

	/**
	 *
	 * @param string $dbname
	 * @throws DataBaseException
	 * @throws InvalidArgumentException
	 */
	public function __construct($dbname)
	{
		if (!preg_match('~^(\w+)://~', $dbname))
		{
			$configFile = new AnydatasetFilenameProcessor ( "_db");
			if (!$configFile->Exists())
			{
				throw new NotFoundException ( "Database file config '_db.anydata.xml' not found!");
			}

			$config = new AnyDataset ( $configFile );
			$filter = new IteratorFilter ( );
			$filter->addRelation ( "dbname", Relation::Equal, $dbname );
			$it = $config->getIterator ( $filter );
			if (! $it->hasNext ())
			{
				throw new DataBaseException ( "Connection string " . $dbname . " not found in _db.anydata.xml config!", 1001 );
			}

			$data = $it->moveNext ();
		}
		else
		{
			$data = new SingleRow();
			$data->AddField('dbtype', 'dsn');
			$data->AddField('dbconnectionstring', $dbname);
		}

		$this->setDbType ( $data->getField ( "dbtype" ) );
		$this->setDbConnectionString ( $data->getField ( "dbconnectionstring" ) );
		//$this->addExtraParam("unixsocket", $data->getField("unixsocket") );
		//$this->addExtraParam("parammodel", $data->getField("parammodel"));

		if ($this->getDbType () == 'dsn')
		{
			/*
		    DSN=DRIVER://USERNAME[:PASSWORD]@SERVER[:PORT]/DATABASE[?KEY1=VALUE1&KEY2=VALUE2&...]

			or

			DSN=DRIVER:///path[?PARAMETERS]

			or

			DSN=DRIVER://C:/PATH[?PARAMETERS]

			------------------
		    PARAMETERS ARE Working:
			    unixsocket - for SQLRelayDriver
				parammodel - ALL
			    protocol - OCI8Driver
			    codepage - OCI8Driver
    		*/

			$patDriver = "(?P<driver>[\w\.]+)\:\/\/";
			$patCredentials = "(?P<username>[\w\.$!%&\-_]+)(?::(?P<password>[\w\.$!%&#\*\+=\[\]\(\)\-_]+))?@";
			$patHost = "(?P<host>[\w\-\.,_]+)(?::(?P<port>\d+))?";
			$patDatabase = "\/(?P<database>[\w\-\.]+)";
			$patExtra = "(?:\?(?P<extraparam>(?:[\w\-\.]+=[\w\-%\.\/]+&?)*))?";
			$patFile = "(?P<path>(?:\w\:)?\/(?:[\w\-\.]+\/?)+)?";

			// Try to parse the connection string.
			$pat = "/$patDriver($patCredentials$patHost$patDatabase|$patFile)$patExtra/i";
			$parts = array();
			if (!preg_match($pat, $this->_dbconnectionstring, $parts))
				throw new InvalidArgumentException("Connection string " . $this->_dbconnectionstring . " is invalid! Please fix it.");

			// Set the Driver
			$this->setDriver ( $parts ['driver'] );

			if (!isset($parts['path']) && !isset($parts['host']))
                                throw new InvalidArgumentException("Connection string " . $this->_dbconnectionstring . " is invalid! Please fix it.");


			// If a path pattern was found set it; otherwise define the database properties
			if (array_key_exists('path', $parts) && (!empty($parts['path'])))
				$this->setFilePath ($parts['path']);
			else
			{
				$this->setUsername ( $parts ['username'] );
				$this->setPassword ( $parts ['password'] );
				$this->setServer ( $parts ['host'] );
				$this->setPort ( $parts ['port'] );
				$this->setDatabase ( $parts ['database'] );
			}

			// If extra param is defined, set it.
			if (array_key_exists('extraparam', $parts) && (!empty($parts['extraparam'])))
			{
				$arrAux = explode('&', $parts['extraparam']);
				foreach($arrAux as $item)
				{
					$aux = explode("=", $item);
					$this->addExtraParam($aux[0], $aux[1]);
				}
			}

			$user = $this->getUsername();
			$pass = $this->getPassword();
		}
		else if ( $this->getDbType() == "literal" )
		{
			$parts = explode("|", $this->_dbconnectionstring);
			$this->_dbconnectionstring = $parts[0];
			$this->setUsername($parts[1]);
			$this->setPassword($parts[2]);
		}
		else if ($this->_dbconnectionstring != "")
		{
			$connection_string = explode( ";", $this->_dbconnectionstring );
			$this->setDriver ( $this->getDbType () );
			$this->setUsername ( $connection_string [1] );
			$this->setPassword ( $connection_string [2] );
			$this->setServer ( $connection_string [0] );
			$this->setDatabase ( $connection_string [3] );
		}
	}

}


?>
