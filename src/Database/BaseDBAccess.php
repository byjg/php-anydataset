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
    private $dataset = null;

    /**
     * Wrapper for SqlHelper
     *
     * @var SqlHelper
     */
    protected $sqlHelper = null;

    /**
     * Base Class Constructor. Don't must be override.
     *
     * @param DBDataset $dbdataset
     */
    public function __construct(DBDataset $dbdataset)
    {
        $this->dataset = $dbdataset;
    }

    /**
     * Create a instance of DBDataset to connect database
     * @return DBDataset
     */
    protected function getDBDataset()
    {
        return $this->dataset;
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

        $insertedId = null;
        if ($getId) {
            $insertedId = $dbfunction->executeAndGetInsertedId($this->getDBDataset(), $sql, $param);
        } else {
            $this->getDBDataset()->execSQL($sql, $param);
        }

        if ($debug) {
            $end = microtime(true);
            $log->debug("Execution time: " . ($end - $start) . " seconds ");
        }

        return $insertedId;
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
     * @return SqlHelper
     */
    public function getSQLHelper()
    {
        if (is_null($this->sqlHelper)) {
            $this->sqlHelper = new SqlHelper($this->getDBDataset());
        }

        return $this->sqlHelper;
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
     * @param IteratorInterface $iterator
     * @param string $key
     * @param string $value
     * @param string $firstElement
     * @return array
     */
    public static function getArrayFromIterator(IteratorInterface $iterator, $key, $value, $firstElement = "-- Selecione --")
    {
        $retArray = array();
        if ($firstElement != "") {
            $retArray[""] = $firstElement;
        }
        while ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $retArray[$singleRow->getField(strtolower($key))] = $singleRow->getField(strtolower($value));
        }
        return $retArray;
    }

    /**

     * @param IteratorInterface $iterator
     * @param string $name
     * @param array $fields
     * @param bool $echoToBrowser
     * @return string
     */
    public static function saveToCSV($iterator, $name = "data.csv", $fields = null, $echoToBrowser = true)
    {
        if ($echoToBrowser) {
            ob_clean();

            header("Content-Type: text/csv;");
            header("Content-Disposition: attachment; filename=$name");
        }

        $first = true;
        $line = "";
        foreach ($iterator as $singleRow) {
            if ($first) {
                $first = false;

                if (is_null($fields)) {
                    $fields = $singleRow->getFieldNames();
                }

                $line .= '"' . implode('","', $fields) . '"' . "\n";
            }

            $raw = array();
            foreach ($fields as $field) {
                $raw[] = $singleRow->getField($field);
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
