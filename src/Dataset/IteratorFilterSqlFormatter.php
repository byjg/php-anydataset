<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Enum\Relation;
use ByJG\AnyDataset\Store\Helpers\SqlHelper;

class IteratorFilterSqlFormatter extends IteratorFilterFormatter
{
    public function format($filters, $tableName = null, &$params = [], $returnFields = "*")
    {
        $params = array();

        $sql = "select @@returnFields from @@tableName ";
        $sqlFilter = $this->getFilter($filters, $params);
        if ($sqlFilter != "") {
            $sql .= " where @@sqlFilter ";
        }

        $sql = SqlHelper::createSafeSQL(
            $sql,
            [
                "@@returnFields" => $returnFields,
                "@@tableName" => $tableName,
                "@@sqlFilter" => $sqlFilter
            ]
        );

        return $sql;
    }

    public function getRelation($name, $relation, $value, &$param)
    {
        $value = trim($value);
        $paramName = $name;
        $counter = 0;
        while (array_key_exists($paramName, $param)) {
            $paramName = $name . ($counter++);
        }

        $param[$paramName] = $value;

        $result = "";
        $field = " $name ";
        $valueparam = " [[" . $paramName . "]] ";
        switch ($relation) {
            case Relation::EQUAL:
                $result = $field . "=" . $valueparam;
                break;

            case Relation::GREATER_THAN:
                $result = $field . ">" . $valueparam;
                break;

            case Relation::LESS_THAN:
                $result = $field . "<" . $valueparam;
                break;

            case Relation::GREATER_OR_EQUAL_THAN:
                $result = $field . ">=" . $valueparam;
                break;

            case Relation::LESS_OR_EQUAL_THAN:
                $result = $field . "<=" . $valueparam;
                break;

            case Relation::NOT_EQUAL:
                $result = $field . "!=" . $valueparam;
                break;

            case Relation::STARTS_WITH:
                $param[$paramName] = $value . "%";
                $result = $field . " like " . $valueparam;
                break;

            case Relation::CONTAINS:
                $param[$paramName] = "%" . $value . "%";
                $result = $field . " like " . $valueparam;
                break;

        }

        return $result;
    }
}
