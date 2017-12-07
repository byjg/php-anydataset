<?php

namespace ByJG\AnyDataset\Store;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\AnyDataset\Exception\RouteNotFoundException;
use ByJG\AnyDataset\Exception\RouteNotMatchedException;
use ByJG\AnyDataset\Factory;

class Route implements DbDriverInterface
{

    /**
     * @var DbDriverInterface[]
     */
    protected $dbDriverInterface = [];

    /**
     * @var string[]
     */
    protected $routes;

    /**
     * Route constructor.
     */
    public function __construct()
    {
    }

    //<editor-fold desc="Route Methods">

    /**
     * @param string $routeName
     * @param DbDriverInterface[]|DbDriverInterface|string|string[] $dbDriver
     * @return \ByJG\AnyDataset\Store\Route
     */
    public function addDbDriverInterface($routeName, $dbDriver)
    {
        if (!isset($this->dbDriverInterface[$routeName])) {
            $this->dbDriverInterface[$routeName] = [];
        }

        if (!is_array($dbDriver)) {
            $dbDriver = [$dbDriver];
        }

        foreach ($dbDriver as $item) {
            $this->dbDriverInterface[$routeName][] = $item;
        }

        return $this;
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForSelect($routeName, $table = null)
    {
        if (empty($table)) {
            $table = '\w+';
        }
        return $this->addCustomRoute($routeName, '^select.*from\s+([`]?' . $table . '[`]?)\s');
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForInsert($routeName, $table = null)
    {
        if (empty($table)) {
            $table = '\w+';
        }
        return $this->addCustomRoute($routeName, '^insert\s+into\s+([`]?' . $table . '[`]?)\s+\(');
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForUpdate($routeName, $table = null)
    {
        if (empty($table)) {
            $table = '\w+';
        }
        return $this->addCustomRoute($routeName, '^update\s+([`]?' . $table . '[`]?)\s+set');
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForDelete($routeName, $table = null)
    {
        if (empty($table)) {
            $table = '\w+';
        }
        return $this->addCustomRoute($routeName, '^delete\s+(from\s+)?([`]?' . $table . '[`]?)\s');
    }

    /**
     * @param $routeName
     * @param $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForTable($routeName, $table)
    {
        $this->addRouteForRead($routeName, $table);
        $this->addRouteForWrite($routeName, $table);
        return $this;
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForWrite($routeName, $table = null)
    {
        $this->addRouteForInsert($routeName, $table);
        $this->addRouteForUpdate($routeName, $table);
        $this->addRouteForDelete($routeName, $table);
        return $this;
    }

    /**
     * @param $routeName
     * @param null $table
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForRead($routeName, $table = null)
    {
        return $this->addRouteForSelect($routeName, $table);
    }

    /**
     * @param $routeName
     * @param $field
     * @param $value
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addRouteForFilter($routeName, $field, $value)
    {
        return $this->addCustomRoute($routeName, "\\s`?$field`?\\s*=\\s*'?$value'?\s");
    }

    /**
     * @param $routeName
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addDefaultRoute($routeName)
    {
        return $this->addCustomRoute($routeName, '.');
    }

    /**
     * @param $routeName
     * @param $regEx
     * @return \ByJG\AnyDataset\Store\Route
     * @throws \ByJG\AnyDataset\Exception\RouteNotFoundException
     */
    public function addCustomRoute($routeName, $regEx)
    {
        if (!isset($this->dbDriverInterface[$routeName])) {
            throw new RouteNotFoundException("Invalid route $routeName");
        }
        $this->routes[$regEx] = $routeName;
        return $this;
    }

    /**
     * @param $sql
     * @return DbDriverInterface
     * @throws \ByJG\AnyDataset\Exception\RouteNotMatchedException
     */
    public function matchRoute($sql)
    {
        $sql = trim(strtolower(str_replace("\n", " ", $sql))) . ' ';
        foreach ($this->routes as $pattern => $routeName) {
            if (!preg_match("/$pattern/", $sql)) {
                continue;
            }

            $dbDriver = $this->dbDriverInterface[$routeName][rand(0, count($this->dbDriverInterface[$routeName])-1)];
            if (is_string($dbDriver)) {
                return Factory::getDbRelationalInstance($dbDriver);
            }

            return $dbDriver;
        }

        throw new RouteNotMatchedException('Route not matched');
    }
    //</editor-fold>

    //<editor-fold desc="DbDriverInterface">

    /**
     * @param string $sql
     * @param null $params
     * @return \ByJG\AnyDataset\Dataset\GenericIterator
     * @throws \ByJG\AnyDataset\Exception\RouteNotMatchedException
     */
    public function getIterator($sql, $params = null)
    {
        $dbDriver = $this->matchRoute($sql);
        return $dbDriver->getIterator($sql, $params);
    }

    /**
     * @param $sql
     * @param null $array
     * @return mixed
     * @throws \ByJG\AnyDataset\Exception\RouteNotMatchedException
     */
    public function getScalar($sql, $array = null)
    {
        $dbDriver = $this->matchRoute($sql);
        return $dbDriver->getScalar($sql, $array);
    }

    /**
     * @param $tablename
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function getAllFields($tablename)
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @param $sql
     * @param null $array
     * @return mixed
     * @throws \ByJG\AnyDataset\Exception\RouteNotMatchedException
     */
    public function execute($sql, $array = null)
    {
        $dbDriver = $this->matchRoute($sql);
        return $dbDriver->execute($sql, $array);
    }

    /**
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function beginTransaction()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function commitTransaction()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function rollbackTransaction()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @return \PDO|void
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function getDbConnection()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @param $name
     * @param $value
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function setAttribute($name, $value)
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @param $name
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function getAttribute($name)
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @param $sql
     * @param null $array
     * @return mixed
     * @throws \ByJG\AnyDataset\Exception\RouteNotMatchedException
     */
    public function executeAndGetId($sql, $array = null)
    {
        $dbDriver = $this->matchRoute($sql);
        return $dbDriver->executeAndGetId($sql, $array);
    }

    /**
     * @return \ByJG\AnyDataset\DbFunctionsInterface|void
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function getDbHelper()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @return void
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function getUri()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function isSupportMultRowset()
    {
        throw new NotImplementedException('Feature not available');
    }

    /**
     * @param $multipleRowSet
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public function setSupportMultRowset($multipleRowSet)
    {
        throw new NotImplementedException('Feature not available');
    }
    //</editor-fold>
}
