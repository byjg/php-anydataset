<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Enum\Relation;

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
     * @param $array
     * @return SingleRow[]
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
     * @param SingleRow $singleRow
     * @return string
     */
    private function evalString(SingleRow $singleRow)
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

            $field = $singleRow->getField($name);

            if (!is_array($field)) {
                $field = [$field];
            }

            foreach ($field as $valueparam) {
                switch ($relation) {
                    case Relation::EQUAL:
                        $result[$pos] &= ($valueparam == $value);
                        break;

                    case Relation::GREATER_THAN:
                        $result[$pos] &= ($valueparam > $value);
                        break;

                    case Relation::LESS_THAN:
                        $result[$pos] &= ($valueparam < $value);
                        break;

                    case Relation::GREATER_OR_EQUAL_THAN:
                        $result[$pos] &= ($valueparam >= $value);
                        break;

                    case Relation::LESS_OR_EQUAL_THAN:
                        $result[$pos] &= ($valueparam <= $value);
                        break;

                    case Relation::NOT_EQUAL:
                        $result[$pos] &= ($valueparam != $value);
                        break;

                    case Relation::STARTS_WITH:
                        $result[$pos] &= (strpos($valueparam, $value) === 0);
                        break;

                    case Relation::CONTAINS:
                        $result[$pos] &= (strpos($valueparam, $value) !== false);
                        break;

                }
            }
        }

        $finalResult |= $result[$pos];

        return $finalResult;
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return void
     * @desc Add a single string comparison to filter.
     */
    public function addRelation($name, $relation, $value)
    {
        $this->filters[] = [" and ", $name, $relation, $value];
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return void
     * @desc Add a single string comparison to filter. This comparison use the OR operator.
     */
    public function addRelationOr($name, $relation, $value)
    {
        $this->filters[] = [" or ", $name, $relation, $value];
    }

    /**
     * Add a "("

     */
    public function startGroup()
    {
        $this->filters[] = ["(", "", "", ""];
    }

    /**
     * Add a ")"

     */
    public function endGroup()
    {
        $this->filters[] = [")", "", "", ""];
    }

    public function getRawFilters()
    {
        return $this->filters;
    }
}
