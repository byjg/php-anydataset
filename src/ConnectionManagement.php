<?php

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Exception\DatabaseException;
use InvalidArgumentException;

class ConnectionManagement
{

    private $dbconnectionstring;

    public function setDbConnectionString($value)
    {
        $this->dbconnectionstring = $value;
    }

    public function getDbConnectionString()
    {
        return $this->dbconnectionstring;
    }

    private $driver;

    public function setDriver($value)
    {
        $this->driver = $value;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    private $username;

    public function setUsername($value)
    {
        $this->username = $value;
    }

    public function getUsername()
    {
        return $this->username;
    }

    private $password;

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    private $server;

    public function setServer($value)
    {
        $this->server = $value;
    }

    public function getServer()
    {
        return $this->server;
    }

    private $port = "";

    public function setPort($value)
    {
        $this->port = $value;
    }

    public function getPort()
    {
        return $this->port;
    }

    private $database;

    public function setDatabase($value)
    {
        $this->database = $value;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    private $extraParam = array();

    public function addExtraParam($key, $value)
    {
        $this->extraParam[$key] = $value;
    }

    public function getExtraParam($key)
    {
        if (array_key_exists($key, $this->extraParam)) {
            return $this->extraParam[$key];
        } else {
            return "";
        }
    }

    private $file;

    public function setFilePath($value)
    {
        $this->file = $value;
    }

    public function getFilePath()
    {
        return $this->file;
    }

    /**
     * The connection string must be defined in the config file 'config/anydataset.php'
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
        if (!preg_match('~^(\w+)://~', $dbname)) {
            $config = AnyDatasetContext::getInstance()->getConnectionString($dbname);
        }

        $this->setDbConnectionString($config['url']);

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
            throw new InvalidArgumentException(
                "Connection string " . $this->getDbConnectionString() . " is invalid! Please fix it."
            );
        }

        // Set the Driver
        $this->setDriver($parts['driver']);

        if (!isset($parts['path']) && !isset($parts['host'])) {
            throw new InvalidArgumentException(
                "Connection string " . $this->getDbConnectionString() . " is invalid! Please fix it."
            );
        }
        
        // If a path pattern was found set it; otherwise define the database properties
        if (array_key_exists('path', $parts) && (!empty($parts['path']))) {
            $this->setFilePath($parts['path']);
        } else {
            $this->setUsername(empty($parts['username']) ? $parts['username2'] : $parts['username']);
            $this->setPassword(isset($parts['password']) ? $parts['password'] : '');
            $this->setServer($parts['host']);
            $this->setPort(isset($parts['port']) ? $parts['port'] : '');
            $this->setDatabase(isset($parts['database']) ? $parts['database'] : '');
        }

        // If extra param is defined, set it.
        if (array_key_exists('extraparam', $parts) && (!empty($parts['extraparam']))) {
            $arrAux = explode('&', $parts['extraparam']);
            foreach ($arrAux as $item) {
                $aux = explode("=", $item);
                $this->addExtraParam($aux[0], $aux[1]);
            }
        }
    }
}
