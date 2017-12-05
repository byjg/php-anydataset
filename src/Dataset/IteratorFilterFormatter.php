<?php

namespace ByJG\AnyDataset\Dataset;

abstract class IteratorFilterFormatter
{

    abstract public function getRelation($name, $relation, $value, &$param);

    abstract public function format($filters, $tableName = null, &$params = [], $returnFields = "*");

    public function getFilter($filters, &$param)
    {
        $filter = "";
        $param = array();

        $previousValue = null;
        foreach ($filters as $value) {
            if ($value[0] == "(") {
                if (!is_null($previousValue)) {
                    $filter .= " or ( ";
                } else {
                    $filter .= " ( ";
                }
            } elseif ($value[0] == ")") {
                $filter .= ")";
            } else {
                if ((!is_null($previousValue)) && ($previousValue[0] != "(")) {
                    $filter .= $value[0];
                }
                $filter .= $this->getRelation($value[1], $value[2], $value[3], $param);
            }
            $previousValue = $value;
        }

        return $filter;
    }
}
