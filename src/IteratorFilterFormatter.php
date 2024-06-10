<?php

namespace ByJG\AnyDataset\Core;

abstract class IteratorFilterFormatter
{

    /**
     * Get Relation
     *
     * @param string $name
     * @param string $relation
     * @param array|string $value
     * @param array $param
     * @return string
     */
    abstract public function getRelation(string $name, string $relation, mixed $value, array &$param): string;

    /**
     * Get formatted field
     *
     * @param array $filters
     * @param string|null $tableName
     * @param array $params
     * @param string $returnFields
     * @return string
     */
    abstract public function format(array $filters, string $tableName = null, array &$params = [], string $returnFields = "*"): string;

    /**
     * Get Filter
     *
     * @param array $filters
     * @param array $param
     * @return string
     */
    public function getFilter(array $filters, array &$param): string
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
