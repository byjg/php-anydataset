<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\AnyDataset\Repository\DBIterator;
use PDO;
use PDOStatement;

class DbPdoDriver implements DbDriverInterface
{

    /**
     * @var PDO
     */
    protected $instance = null;

    /**
     * @var ConnectionManagement
     */
    protected $connectionManagement;

    public function __construct(ConnectionManagement $connMngt, $strcnn, $preOptions, $postOptions)
    {
        $this->connectionManagement = $connMngt;

        if (is_null($strcnn)) {
            if ($this->connectionManagement->getFilePath() != "") {
                $strcnn = $this->connectionManagement->getDriver() . ":" . $this->connectionManagement->getFilePath();
            } else {
                $strcnn = $this->connectionManagement->getDriver() . ":dbname=" .
                    $this->connectionManagement->getDatabase();
                if ($this->connectionManagement->getExtraParam("unixsocket") != "") {
                    $strcnn .= ";unix_socket=" . $this->connectionManagement->getExtraParam("unixsocket");
                } else {
                    $strcnn .= ";host=" . $this->connectionManagement->getServer();
                    if ($this->connectionManagement->getPort() != "") {
                        $strcnn .= ";port=" . $this->connectionManagement->getPort();
                    }
                }
            }
        }

        // Create Connection
        $this->instance = new PDO(
            $strcnn,
            $this->connectionManagement->getUsername(),
            $this->connectionManagement->getPassword(),
            (array) $preOptions
        );
        $this->connectionManagement->setDriver($this->instance->getAttribute(PDO::ATTR_DRIVER_NAME));

        // Set Specific Attributes
        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        foreach ((array) $postOptions as $key => $value) {
            $this->instance->setAttribute($key, $value);
        }
    }

    public static function factory(ConnectionManagement $connMngt)
    {
        if (!defined('PDO::ATTR_DRIVER_NAME')) {
            throw new NotAvailableException("Extension 'PDO' is not loaded");
        }

        if (!extension_loaded('pdo_' . strtolower($connMngt->getDriver()))) {
            throw new NotAvailableException("Extension 'pdo_" . strtolower($connMngt->getDriver()) . "' is not loaded");
        }

        $class = '\ByJG\AnyDataset\Database\Pdo' . ucfirst($connMngt->getDriver());

        if (!class_exists($class, true)) {
            return new DbPdoDriver($connMngt, null, null, null);
        } else {
            return new $class($connMngt);
        }
    }

    public function __destruct()
    {
        $this->instance = null;
    }

    /**
     *
     * @param string $sql
     * @param array $array
     * @return PDOStatement
     */
    protected function getDBStatement($sql, $array = null)
    {
        if ($array) {
            list($sql, $array) = SqlBind::parseSQL($this->connectionManagement, $sql, $array);
            $stmt = $this->instance->prepare($sql);
            foreach ($array as $key => $value) {
                $stmt->bindValue(":" . SqlBind::keyAdj($key), $value);
            }
        } else {
            $stmt = $this->instance->prepare($sql);
        }

        return $stmt;
    }

    public function getIterator($sql, $array = null)
    {
        $stmt = $this->getDBStatement($sql, $array);
        $stmt->execute();
        $iterator = new DBIterator($stmt);
        return $iterator;
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
        $fields = array();
        $rs = $this->instance->query(SqlHelper::createSafeSQL("select * from :table where 0=1", array(":table" => $tablename)));
        $fieldLength = $rs->columnCount();
        for ($i = 0; $i < $fieldLength; $i++) {
            $fld = $rs->getColumnMeta($i);
            $fields [] = strtolower($fld ["name"]);
        }
        return $fields;
    }

    public function beginTransaction()
    {
        $this->instance->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->instance->commit();
    }

    public function rollbackTransaction()
    {
        $this->instance->rollBack();
    }

    public function executeSql($sql, $array = null)
    {
        $stmt = $this->getDBStatement($sql, $array);
        $result = $stmt->execute();
        return $result;
    }

    /**
     *
     * @return PDO
     */
    public function getDbConnection()
    {
        return $this->instance;
    }

    public function getAttribute($name)
    {
        $this->instance->getAttribute($name);
    }

    public function setAttribute($name, $value)
    {
        $this->instance->setAttribute($name, $value);
    }
}
