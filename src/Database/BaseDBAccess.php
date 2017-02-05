<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Database\Expressions\DbFunctionsInterface;
use ByJG\AnyDataset\Repository\DBDataset;
use ByJG\AnyDataset\Repository\IteratorInterface;
use Psr\Log\LoggerInterface;

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

    private $logger = null;

    /**
     * Base Class Constructor. Don't must be override.
     *
     * @param DBDataset $dbdataset
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(DBDataset $dbdataset, LoggerInterface $logger = null)
    {
        $this->dataset = $dbdataset;
        $this->logger = $logger;
    }

    /**
     * Create a instance of DBDataset to connect database
     * @return DBDataset
     */
    protected function getDBDataset()
    {
        return $this->dataset;
    }

    protected function logStart($sql, $param)
    {
        $start = 0;
        if (!is_null($this->logger)) {
            $this->logger->debug("Class name: " . get_class($this));
            $this->logger->debug("SQL: " . $sql);
            if (!is_null($param)) {
                $strForLog = "";
                foreach ($param as $key => $value) {
                    if ($strForLog != "") {
                        $strForLog .= ", ";
                    }
                    $strForLog .= "[$key]=$value";
                }
                $this->logger->debug("Params: $strForLog");
            }
            $start = microtime(true);
        }
        return $start;
    }

    protected function logEnd($startTime)
    {
        if (!is_null($this->logger)) {
            $end = microtime(true);
            $this->logger->debug("Execution time: " . ($end - $startTime) . " seconds ");
        }
    }

    /**
     * Execute a SQL and dont wait for a response.
     * @param string $sql
     * @param array $param
     * @param bool $getId
     * @return int|null
     */
    protected function executeSQL($sql, $param = null, $getId = false)
    {
        $start = $this->logStart($sql, $param);

        $insertedId = null;
        if ($getId) {
            $insertedId = $this->getDbFunctions()->executeAndGetInsertedId($this->getDBDataset(), $sql, $param);
        } else {
            $this->getDBDataset()->execSQL($sql, $param);
        }

        $this->logEnd($start);

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
        $start = $this->logStart($sql, $param);

        $iterator = $this->getDBDataset()->getIterator($sql, $param, $ttl);

        $this->logEnd($start);

        return $iterator;
    }

    protected function getScalar($sql, $param = null)
    {
        $start = $this->logStart($sql, $param);

        $scalar = $this->getDBDataset()->getScalar($sql, $param);

        $this->logEnd($start);

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
}
