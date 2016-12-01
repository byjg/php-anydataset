<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\AnyDatasetContext;
use ByJG\AnyDataset\Database\Expressions\DbFunctionsInterface;
use ByJG\AnyDataset\LogHandler;
use ByJG\AnyDataset\Repository\DBDataset;
use ByJG\AnyDataset\Repository\IteratorInterface;

class BaseDBAccess
{

    /**
     * @var DBDataset
     */
    private $_db = null;

    /**
     * Wrapper for SqlHelper

     *
*@var SqlHelper
     */
    protected $_sqlhelper = null;

    /**
     * Base Class Constructor. Don't must be override.
     * @param DBDataset $db
     */
    public function __construct(DBDataset $db)
    {
        $this->_db = $db;
    }

    /**
     * Create a instance of DBDataset to connect database
     * @return DBDataset
     */
    protected function getDBDataset()
    {
        return $this->_db;
    }

    /**
     * Execute a SQL and dont wait for a response.
     * @param string $sql
     * @param string $param
     * @param bool $getId
     * @return int|null
     */
    protected function executeSQL($sql, $param = null, $getId = false)
    {
        $log = null;
        $dbfunction = $this->getDbFunctions();

        $debug = $this->getDebug();
        $start = 0;
        if ($debug) {
            $log = LogHandler::getInstance();
            $log->debug("Class name: " . get_class($this));
            $log->debug("SQL: " . $sql);
            if (!is_null($param)) {
                $strForLog = "";
                foreach ($param as $key => $value) {
                    if ($strForLog != "") {
                        $strForLog .= ", ";
                    }
                    $strForLog .= "[$key]=$value";
                }
                $log->debug("Params: $strForLog");
            }
            $start = microtime(true);
        }

        if ($getId) {
            $id = $dbfunction->executeAndGetInsertedId($this->getDBDataset(), $sql, $param);
        } else {
            $id = null;
            $this->getDBDataset()->execSQL($sql, $param);
        }

        if ($debug) {
            $end = microtime(true);
            $log->debug("Execution time: " . ($end - $start) . " seconds ");
        }

        return $id;
    }

    /**
     * Execulte SELECT SQL Query
     *
     * @param string $sql
     * @param array $param
     * @param int $ttl
     * @return IteratorInterface
     */
    protected function getIterator($sql, $param = null, $ttl = null)
    {
        $log = null;
        $debug = $this->getDebug();
        $start = 0;
        if ($debug) {
            $log = LogHandler::getInstance();
            $log->debug("Class name: " . get_class($this));
            $log->debug("SQL: " . $sql);
            if (!is_null($param)) {
                $strForLog = "";
                foreach ($param as $key => $value) {
                    if ($strForLog != "") {
                        $strForLog .= ", ";
                    }
                    $strForLog .= "[$key]=$value";
                }
                $log->debug("Params: $strForLog");
            }
            $start = microtime(true);
        }
        $iterator = $this->getDBDataset()->getIterator($sql, $param, $ttl);
        if ($debug) {
            $end = microtime(true);
            $log->debug("Execution Time: " . ($end - $start) . " segundos ");
        }
        return $iterator;
    }

    protected function getScalar($sql, $param = null)
    {
        $log = null;
        $debug = $this->getDebug();
        $start = 0;
        if ($debug) {
            $log = LogHandler::getInstance();
            $log->debug("Class name: " . get_class($this));
            $log->debug("SQL: " . $sql);
            if (!is_null($param)) {
                $strForLog = "";
                foreach ($param as $key => $value) {
                    if ($strForLog != "") {
                        $strForLog .= ", ";
                    }
                    $strForLog .= "[$key]=$value";
                }
                $log->debug("Params: $strForLog");
            }
            $start = microtime(true);
        }
        $scalar = $this->getDBDataset()->getScalar($sql, $param);
        if ($debug) {
            $end = microtime(true);
            $log->debug("Execution Time: " . ($end - $start) . " segundos ");
        }
        return $scalar;
    }

    /**
     * Get a SqlHelper object

     *
*@return SqlHelper
     */
    public function getSQLHelper()
    {
        if (is_null($this->_sqlhelper)) {
            $this->_sqlhelper = new SqlHelper($this->getDBDataset());
        }

        return $this->_sqlhelper;
    }

    /**
     * Get an Interator from an ID. Ideal for get data from PK
     *
     * @param string $tablename
     * @param string $key
     * @param string $value
     * @return IteratorInterface
     */
    protected function getIteratorbyId($tablename, $key, $value)
    {
        $sql = "select * from $tablename where $key = [[$key]] ";
        $param = array();
        $param[$key] = $value;
        return $this->getIterator($sql, $param);
    }

    /**
     * Get an Array from an existing Iterator
     *
     * @param IteratorInterface $it
     * @param string $key
     * @param string $value
     * @param string $firstElement
     * @return array
     */
    public static function getArrayFromIterator(IteratorInterface $it, $key, $value, $firstElement = "-- Selecione --")
    {
        $retArray = array();
        if ($firstElement != "") {
            $retArray[""] = $firstElement;
        }
        while ($it->hasNext()) {
            $sr = $it->moveNext();
            $retArray[$sr->getField(strtolower($key))] = $sr->getField(strtolower($value));
        }
        return $retArray;
    }

    /**
     *
     * @param IteratorInterface $it
     * @param string $name
     * @param array $fields
     * @param bool $echoToBrowser
     * @return string
     */
    public static function saveToCSV($it, $name = "data.csv", $fields = null, $echoToBrowser = true)
    {
        if ($echoToBrowser) {
            ob_clean();

            header("Content-Type: text/csv;");
            header("Content-Disposition: attachment; filename=$name");
        }

        $first = true;
        $line = "";
        foreach ($it as $sr) {
            if ($first) {
                $first = false;

                if (is_null($fields)) {
                    $fields = $sr->getFieldNames();
                }

                $line .= '"' . implode('","', $fields) . '"' . "\n";
            }

            $raw = array();
            foreach ($fields as $field) {
                $raw[] = $sr->getField($field);
            }
            $line .= '"' . implode('","', array_values($raw)) . '"' . "\n";

            if ($echoToBrowser) {
                echo $line;
                $line = "";
            }
        }

        if (!$echoToBrowser) {
            return $line;
        }
        
        return null;
    }

    /**
     * Get a IDbFunctions class containing specific database operations
     * @return DbFunctionsInterface
     */
    public function getDbFunctions()
    {
        return $this->getDBDataset()->getDbFunctions();
    }

    public function beginTransaction()
    {
        $this->getDBDataset()->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->getDBDataset()->commitTransaction();
    }

    public function rollbackTransaction()
    {
        $this->getDBDataset()->rollbackTransaction();
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return AnyDatasetContext::getInstance()->getDebug();
    }
}
