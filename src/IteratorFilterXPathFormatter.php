<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;

class IteratorFilterXPathFormatter extends IteratorFilterFormatter
{
    public function format($filters, $tableName = null, &$params = [], $returnFields = "*")
    {
        $param = "";
        $xpathFilter = $this->getFilter($filters, $param);

        if ($xpathFilter == "") {
            return "/anydataset/row";
        }

        return "/anydataset/row[" . $xpathFilter . "]";
    }

    public function getRelation($name, $relation, $value, &$param)
    {
        $str = is_numeric($value) ? "" : "'";
        $field = "field[@name='" . $name . "'] ";
        $value = " $str$value$str ";

        $data = [
            Relation::EQUAL => function ($field, $value) {
                 return $field . "=" . $value;
            },

            Relation::GREATER_THAN => function ($field, $value) {
                 return $field . ">" . $value;
            },

            Relation::LESS_THAN => function ($field, $value) {
                 return $field . "<" . $value;
            },

            Relation::GREATER_OR_EQUAL_THAN => function ($field, $value) {
                 return $field . ">=" . $value;
            },

            Relation::LESS_OR_EQUAL_THAN => function ($field, $value) {
                 return $field . "<=" . $value;
            },

            Relation::NOT_EQUAL => function ($field, $value) {
                 return $field . "!=" . $value;
            },

            Relation::STARTS_WITH => function ($field, $value) {
                 return " starts-with($field, $value) ";
            },

            Relation::CONTAINS => function ($field, $value) {
                 return " contains($field, $value) ";
            },
        ];

        return $data[$relation]($field, $value);
    }
}
