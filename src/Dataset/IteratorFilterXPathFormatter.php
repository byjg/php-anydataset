<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Enum\Relation;

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

        $result = "";
        switch ($relation) {
            case Relation::EQUAL:
                $result = $field . "=" . $value;
                break;

            case Relation::GREATER_THAN:
                $result = $field . ">" . $value;
                break;

            case Relation::LESS_THAN:
                $result = $field . "<" . $value;
                break;

            case Relation::GREATER_OR_EQUAL_THAN:
                $result = $field . ">=" . $value;
                break;

            case Relation::LESS_OR_EQUAL_THAN:
                $result = $field . "<=" . $value;
                break;

            case Relation::NOT_EQUAL:
                $result = $field . "!=" . $value;
                break;

            case Relation::STARTS_WITH:
                $result = " starts-with($field, $value) ";
                break;

            case Relation::CONTAINS:
                $result = " contains($field, $value) ";
                break;

        }
        return $result;
    }
}
