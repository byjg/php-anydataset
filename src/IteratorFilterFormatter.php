<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;

class IteratorFilterFormatter
{

    /**
     * Get Relation
     *
     * @param string $name
     * @param Relation $relation
     * @param array|string $value
     * @param array $param
     * @return string
     */
    public function getRelation(string $name, Relation $relation, mixed $value, array &$param): string
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = is_numeric($val) ? $val : "'$val'";
            }
            $value = "[" . implode(",", $value) . "]";
        } else {
            $value = is_numeric($value) ? $value : "'$value'";
        }

        switch ($relation) {
            case Relation::EQUAL:
                $return = "%$name == $value";
                break;

            case Relation::GREATER_THAN:
                $return = "%$name > $value";
                break;

            case Relation::LESS_THAN:
                $return = "%$name < $value";
                break;

            case Relation::GREATER_OR_EQUAL_THAN:
                $return = "%$name >= $value";
                break;

            case Relation::LESS_OR_EQUAL_THAN:
                $return = "%$name <= $value";
                break;

            case Relation::NOT_EQUAL:
                $return = "%$name != $value";
                break;

            case Relation::STARTS_WITH:
                $return = " str_starts_with(%$name, $value) ";
                break;

            case Relation::IN:
                $return = " in_array(%$name, $value) ";
                break;

            case Relation::NOT_IN:
                $return = " !in_array(%$name, $value) ";
                break;

            default: // Relation::CONTAINS:
                $return = " str_contains(%$name, $value) ";
                break;
        }

        return $return;
    }

    /**
     * Get formatted field
     *
     * @param array $filters
     * @param string|null $tableName
     * @param array $params
     * @param string $returnFields
     * @return string
     */
    public function format(array $filters, string $tableName = null, array &$params = [], string $returnFields = "*"): string
    {
        return $this->getFilter($filters, $params);
    }

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

        $first = true;
        foreach ($filters as $value) {
            if (!$first || $value[0] == "(") {
                $filter .= $value[0];
            }
            $first = false;
            if ($value[0] == ")") {
                continue;
            }
            $filter .= $this->getRelation($value[1], $value[2], $value[3], $param);
        }

        return $filter;
    }
}
