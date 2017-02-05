<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Database\Expressions\DbBaseFunctions;
use ByJG\AnyDataset\Enum\SQLFieldType;
use ByJG\AnyDataset\Enum\SQLType;
use ByJG\AnyDataset\Repository\DBDataset;
use ByJG\AnyDataset\Repository\SingleRow;
use DateTime;
use Exception;

class SqlHelper
{

    /**
     * @var DBDataset
     */
    private $dataset;
    protected $fieldDeliLeft = " ";
    protected $fieldDeliRight = " ";

    /**
     * @param DBDataset $dataset
     */
    public function __construct(DBDataset $dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * Generate and Execute UPDATE and INSERTS
     *
     * @param string $table
     * @param array $fields
     * @param $param
     * @param SQLType|int $type
     * @param string $filter
     * @param string $decimalpoint
     * @return string
     * @throws Exception
     */
    public function generateSQL(
        $table,
        $fields,
        &$param,
        $type = SQLType::SQL_INSERT,
        $filter = "",
        $decimalpoint = "."
    ) {
        if ($fields instanceof SingleRow) {
            return $this->generateSQL($table, $fields->toArray(), $param, $type, $filter, $decimalpoint);
        }

        if ((is_null($param)) || (!is_array($param))) {
            $param = array();
        }

        $sql = "";
        if ($type == SQLType::SQL_UPDATE) {
            foreach ($fields as $fieldname => $fieldvalue) {
                if ($sql != "") {
                    $sql .= ", ";
                }
                $sql .= " "
                    . $this->fieldDeliLeft
                    . $fieldname
                    . $this->fieldDeliRight
                    . " = "
                    . $this->getValue($fieldname, $fieldvalue, $param, $decimalpoint)
                    . " ";
            }
            $sql = "update $table set $sql where $filter ";
        } elseif ($type == SQLType::SQL_INSERT) {
            $campos = "";
            $valores = "";
            foreach ($fields as $fieldname => $fieldvalue) {
                if ($campos != "") {
                    $campos .= ", ";
                    $valores .= ", ";
                }
                $campos .= $this->fieldDeliLeft . $fieldname . $this->fieldDeliRight;
                $valores .= $this->getValue($fieldname, $fieldvalue, $param, $decimalpoint);
            }
            $sql = "insert into $table ($campos) values ($valores)";
        } elseif ($type == SQLType::SQL_DELETE) {
            if ($filter == "") {
                throw new Exception("I can't generate delete statements without filter");
            }
            $sql = "delete from $table where $filter";
        }
        return $sql;
    }

    /**
     * Generic Function
     *
     * @param string $name
     * @param string $valores
     * @param array $param
     * @param string $decimalpoint
     * @return string
     */
    protected function getValue($name, $valores, &$param, $decimalpoint)
    {
        $paramName = "[[" . $name . "]]";
        if (!is_array($valores)) {
            $valores = array(SQLFieldType::TEXT, $valores);
        }

        if ($valores[0] == SQLFieldType::BOOLEAN) {
            $param[$name] = 'N';
            if ($valores[1] == "1") {
                $param[$name] = 'S';
            }
            return $paramName;
        } elseif (strlen($valores[1]) == 0) { // Zero is Empty!?!?!?!?
            return "null";
        } elseif ($valores[0] == SQLFieldType::TEXT) {
            $param[$name] = trim($valores[1]);
            return $paramName;
        } elseif ($valores[0] == SQLFieldType::DATE) {
            $date = ($valores[1] instanceof DateTime ? $valores[1]->format(DbBaseFunctions::YMDH) : $valores[1]);
            $param[$name] = $date;
            if ($this->getDbDataset()->getConnectionUri()->getDriver() == 'oci8') {
                return "TO_DATE($paramName, 'YYYY-MM-DD')";
            }
            return $paramName;

        } elseif ($valores[0] == SQLFieldType::NUMBER) {
            $search = ($decimalpoint == ".") ? "," : ".";
            $valores[1] = trim(str_replace($search, $decimalpoint, $valores[1]));
            $param[$name] = $valores[1];
            return $paramName;
        }

        return $valores[1];
    }

    /**
     * Used to create a FILTER in a SQL string.
     *
     * @param string $fieldName
     * @param string $relation
     * @param array() $value
     * @param &string $sql (Full SQL)
     * @param &string $param
     */
    public function getWhereClause($fieldName, $relation, $value, &$sql, &$param)
    {
        if (strlen($sql) > 4) {
            $sql .= ' and ';
        }
        $sql = " $fieldName " . $relation . " " . $this->getValue($fieldName, $value, $param, '.');
    }

    public function setFieldDelimeters($left, $right)
    {
        $this->fieldDeliLeft = $left;
        $this->fieldDeliRight = $right;
    }

    public static function createSafeSQL($sql, $list)
    {
        return str_replace(array_keys($list), array_values($list), $sql);
    }

    /**
     * @return DBDataset
     */
    public function getDbDataset()
    {
        return $this->dataset;
    }
}
