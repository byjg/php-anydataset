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

        $data = [
            Relation::EQUAL => function ($name, $paramName) {
                return " $name = [[$paramName]] ";
            },

            Relation::GREATER_THAN => function ($name, $paramName) {
                return " $name > [[$paramName]] ";
            },

            Relation::LESS_THAN => function ($name, $paramName) {
                return " $name < [[$paramName]] ";
            },

            Relation::GREATER_OR_EQUAL_THAN => function ($name, $paramName) {
                return " $name >= [[$paramName]] ";
            },

            Relation::LESS_OR_EQUAL_THAN => function ($name, $paramName) {
                return " $name <= [[$paramName]] ";
            },

            Relation::NOT_EQUAL => function ($name, $paramName) {
                return " $name != [[$paramName]] ";
            },

            Relation::STARTS_WITH => function ($name, $paramName) use (&$param, $value) {
                $param[$paramName] = $value . "%";
                return " $name  like  [[$paramName]] ";
            },

            Relation::CONTAINS => function ($name, $paramName) use (&$param, $value) {
                $param[$paramName] = "%" . $value . "%";
                return " $name  like  [[$paramName]] ";
            }
        ];

        return $data[$relation]($name, $paramName);
    }
}
