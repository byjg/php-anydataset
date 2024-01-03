<?php

namespace ByJG\AnyDataset\Core;

abstract class IteratorFilterFormatter
{

    /**
     * Get Relation
     *
     * @param string $name
     * @param string $relation
     * @param string $value
     * @param array $param
     * @return string
     */
    abstract public function getRelation($name, $relation, $value, &$param);

    /**
     * Get formatted field
     *
     * @param array $filters
     * @param string $tableName
     * @param array $params
     * @param string $returnFields
     * @return string
     */
    abstract public function format($filters, $tableName = null, &$params = [], $returnFields = "*");

    /**
     * Get Filter
     *
     * @param array $filters
     * @param array $param
     * @return string
     */
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
