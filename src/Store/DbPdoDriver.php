<?php

namespace ByJG\AnyDataset\Store;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\AnyDataset\Dataset\DbIterator;
use ByJG\AnyDataset\Factory;
use ByJG\AnyDataset\Helpers\SqlBind;
use ByJG\AnyDataset\Store\Helpers\SqlHelper;
use ByJG\Util\Uri;
use PDO;
use PDOStatement;

abstract class DbPdoDriver implements DbDriverInterface
{

    /**
     * @var PDO
     */
    protected $instance = null;

    /**
     * @var Uri
     */
    protected $connectionUri;

    public function __construct(Uri $connUri, $preOptions = null, $postOptions = null)
    {
        $this->connectionUri = $connUri;

        if (!defined('PDO::ATTR_DRIVER_NAME')) {
            throw new NotAvailableException("Extension 'PDO' is not loaded");
        }

        if (!extension_loaded('pdo_' . strtolower($connUri->getScheme()))) {
            throw new NotAvailableException("Extension 'pdo_" . strtolower($connUri->getScheme()) . "' is not loaded");
        }

        $strcnn = $this->createPboConnStr($connUri);

        // Create Connection
        $this->instance = new PDO(
            $strcnn,
            $this->connectionUri->getUsername(),
            $this->connectionUri->getPassword(),
            (array) $preOptions
        );

        $this->connectionUri->withScheme($this->instance->getAttribute(PDO::ATTR_DRIVER_NAME));

        // Set Specific Attributes
        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        foreach ((array) $postOptions as $key => $value) {
            $this->instance->setAttribute($key, $value);
        }
    }
    
    public function createPboConnStr(Uri $connUri)
    {
        $host = $connUri->getHost();
        if (empty($host)) {
            return $connUri->getScheme() . ":" . $connUri->getPath();
        }
        
        $strcnn = $connUri->getScheme() . ":"
            . "dbname=" . preg_replace('~^/~', '', $connUri->getPath())
            . ";host=" . $connUri->getHost();

        if ($connUri->getPort() != "") {
            $strcnn .= ";port=" . $connUri->getPort();
        }

        $query = $connUri->getQuery();
        $strcnn .= ";" . implode(';', explode('&', $query));

        return $strcnn;
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
            list($sql, $array) = SqlBind::parseSQL($this->connectionUri, $sql, $array);
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
        $iterator = new DbIterator($stmt);
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
        $statement = $this->instance->query(
            SqlHelper::createSafeSQL(
                "select * from :table where 0=1",
                [
                    ":table" => $tablename
                ]
            )
        );
        $fieldLength = $statement->columnCount();
        for ($i = 0; $i < $fieldLength; $i++) {
            $fld = $statement->getColumnMeta($i);
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

    public function executeSqlAndGetId($sql, $array = null)
    {
        $this->getDbHelper()->executeAndGetInsertedId($this, $sql, $array);
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

    protected $dbHelper;

    public function getDbHelper()
    {
        if (empty($this->dbHelper)) {
            $this->dbHelper = Factory::getDbFunctions($this->connectionUri);
        }
        return $this->dbHelper;
    }

    public function getUri()
    {
        return $this->connectionUri;
    }
}
