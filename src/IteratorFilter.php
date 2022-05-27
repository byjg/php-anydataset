<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;

class IteratorFilter
{

    /**
     * @var array
     */
    private $filters;

    /**
     * @desc IteratorFilter Constructor
     */
    public function __construct()
    {
        $this->filters = [];
    }

    /**
     * @return IteratorFilter
     */
    public static function getInstance()
    {
        return new IteratorFilter();
    }

    /**
     * @param $array
     * @return Row[]
     */
    public function match($array)
    {
        $returnArray = [];

        foreach ($array as $sr) {
            if ($this->evalString($sr)) {
                $returnArray[] = $sr;
            }
        }

        return $returnArray;
    }

    public function format(IteratorFilterFormatter $formatter, $tableName = null, &$params = [], $returnFields = "*")
    {
        return $formatter->format($this->filters, $tableName, $params, $returnFields);
    }


    /**
     * @param Row $singleRow
     * @return string
     */
    private function evalString(Row $singleRow)
    {
        $result = [];
        $finalResult = false;
        $pos = 0;

        $result[0] = true;

        foreach ($this->filters as $filter) {
            if (($filter[0] == ")") || ($filter[0] == " or ")) {
                $finalResult |= $result[$pos];
                $result[++$pos] = true;
            }

            $name = $filter[1];
            $relation = $filter[2];
            $value = $filter[3];

            $field = $singleRow->get($name);

            if (!is_array($field)) {
                $field = [$field];
            }

            $data = [
                Relation::EQUAL => function ($valueparam, $value) {
                    return ($valueparam == $value);
                },

                Relation::GREATER_THAN => function ($valueparam, $value) {
                    return ($valueparam > $value);
                },

                Relation::LESS_THAN => function ($valueparam, $value) {
                    return ($valueparam < $value);
                },

                Relation::GREATER_OR_EQUAL_THAN => function ($valueparam, $value) {
                    return ($valueparam >= $value);
                },

                Relation::LESS_OR_EQUAL_THAN => function ($valueparam, $value) {
                    return ($valueparam <= $value);
                },

                Relation::NOT_EQUAL => function ($valueparam, $value) {
                    return ($valueparam != $value);
                },

                Relation::STARTS_WITH => function ($valueparam, $value) {
                    return (strpos($valueparam, $value) === 0);
                },

                Relation::CONTAINS => function ($valueparam, $value) {
                    return (strpos($valueparam, $value) !== false);
                },
            ];

            foreach ($field as $valueparam) {
                $result[$pos] &= $data[$relation]($valueparam, $value);
            }
        }

        $finalResult |= $result[$pos];

        return $finalResult;
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return IteratorFilter
     * @desc Add a single string comparison to filter.
     */
    public function addRelation($name, $relation, $value)
    {
        $this->filters[] = [" and ", $name, $relation, $value];
        return $this;
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return IteratorFilter
     * @desc Add a single string comparison to filter. This comparison use the OR operator.
     */
    public function addRelationOr($name, $relation, $value)
    {
        $this->filters[] = [" or ", $name, $relation, $value];
        return $this;
    }

    /**
     * Add a "("
     * @return IteratorFilter
     */
    public function startGroup()
    {
        $this->filters[] = ["(", "", "", ""];
        return $this;
    }

    /**
     * Add a ")"
     * @return IteratorFilter
     */
    public function endGroup()
    {
        $this->filters[] = [")", "", "", ""];
        return $this;
    }

    public function getRawFilters()
    {
        return $this->filters;
    }
}
