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
     * IteratorFilter Constructor
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
     * @param array $array
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

    /**
     * Get the filter
     *
     * @param IteratorFilterFormatter $formatter
     * @param string $tableName
     * @param array $params
     * @param string $returnFields
     * @return string
     */
    public function format(IteratorFilterFormatter $formatter, $tableName = null, &$params = [], $returnFields = "*")
    {
        return $formatter->format($this->filters, $tableName, $params, $returnFields);
    }


    /**
     * @param Row $singleRow
     * @return bool
     */
    private function evalString(Row $singleRow)
    {
        $result = [];
        $finalResult = false;
        $pos = 0;

        $result[0] = true;

        foreach ($this->filters as $filter) {
            if (($filter[0] == ")") || ($filter[0] == " or ")) {
                $finalResult = $finalResult || $result[$pos];
                $result[++$pos] = true;
            }

            $name = $filter[1];
            $relation = $filter[2];
            $value = $filter[3];

            $field = [$singleRow->get($name)];

            foreach ($field as $valueparam) {
                switch ($relation) {
                    case Relation::EQUAL:
                        $result[$pos] = $result[$pos] && ($valueparam == $value);
                        break;

                    case Relation::GREATER_THAN:
                        $result[$pos] = $result[$pos] && ($valueparam > $value);
                        break;

                    case Relation::LESS_THAN:
                        $result[$pos] = $result[$pos] && ($valueparam < $value);
                        break;

                    case Relation::GREATER_OR_EQUAL_THAN:
                        $result[$pos] = $result[$pos] && ($valueparam >= $value);
                        break;

                    case Relation::LESS_OR_EQUAL_THAN:
                        $result[$pos] = $result[$pos] && ($valueparam <= $value);
                        break;

                    case Relation::NOT_EQUAL:
                        $result[$pos] = $result[$pos] && ($valueparam != $value);
                        break;

                    case Relation::STARTS_WITH:
                        $result[$pos] = $result[$pos] && (strpos(is_null($valueparam) ? "" : $valueparam, $value) === 0);
                        break;

                    default: // Relation::CONTAINS:
                        $result[$pos] = $result[$pos] && (strpos(is_null($valueparam) ? "" : $valueparam, $value) !== false);
                        break;
                }
            }
        }

        $finalResult = $finalResult || $result[$pos];

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

    /**
     * @return array
     */
    public function getRawFilters()
    {
        return $this->filters;
    }
}
