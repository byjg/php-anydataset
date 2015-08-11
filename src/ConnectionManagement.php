<?php

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Exception\DatabaseException;
use InvalidArgumentException;

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
		if (array_key_exists($key, $this->_extraParam)) {
            return $this->_extraParam[$key];
        } else {
            return "";
        }
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
	 * @throws DatabaseException
	 * @throws InvalidArgumentException
	 */
	public function __construct($dbname)
	{

        $config = [
            'url' => $dbname
        ];
		if (!preg_match('~^(\w+)://~', $dbname))
		{
            $config = AnyDatasetContext::getInstance()->getConnectionString($dbname);
		}

		$this->setDbType ( 'dsn' );
		$this->setDbConnectionString ( $config['url'] );

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
        $patCredentials = "(?:((?P<username>\S+):(?P<password>\S+)|(?P<username2>\S+))@)?";
        $patHost = "(?P<host>[\w\-\.,_]+)(?::(?P<port>\d+))?";
        $patDatabase = "(\/(?P<database>[\w\-\.]+))?";
        $patExtra = "(?:\?(?P<extraparam>(?:[\w\-\.]+=[\w\-%\.\/]+&?)*))?";
        $patFile = "(?P<path>(?:\w\:)?\/(?:[\w\-\.]+\/?)+)?";

        // Try to parse the connection string.
        $pat = "/$patDriver($patCredentials$patHost$patDatabase|$patFile)$patExtra/i";
        $parts = array();
        if (!preg_match($pat, $this->getDbConnectionString(), $parts)) {
            throw new InvalidArgumentException("Connection string " . $this->getDbConnectionString() . " is invalid! Please fix it.");
        }

        // Set the Driver
        $this->setDriver ( $parts ['driver'] );

        if (!isset($parts['path']) && !isset($parts['host'])) {
            throw new InvalidArgumentException("Connection string " . $this->getDbConnectionString() . " is invalid! Please fix it.");
        }


        // If a path pattern was found set it; otherwise define the database properties
        if (array_key_exists('path', $parts) && (!empty($parts['path']))) {
            $this->setFilePath($parts['path']);
        } else {
            $this->setUsername(empty($parts ['username']) ? $parts ['username2'] : $parts ['username']);
            $this->setPassword(isset($parts ['password']) ? $parts ['password'] : '');
            $this->setServer($parts ['host']);
            $this->setPort(isset($parts ['port']) ? $parts ['port'] : '');
            $this->setDatabase(isset($parts ['database']) ? $parts ['database'] : '');
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
	}

}

