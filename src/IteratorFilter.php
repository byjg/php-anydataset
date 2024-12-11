<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;

class IteratorFilter
{

    /**
     * @var array
     */
    private array $filters;

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
    public static function getInstance(): IteratorFilter
    {
        return new IteratorFilter();
    }

    /**
     * @param array $array
     * @return Row[]
     */
    public function match(array $array): array
    {
        if (count($this->filters) === 0) {
            return $array;
        }

        $returnArray = [];
        foreach ($array as $sr) {
            $result = $this->evaluateFilter($sr, $this->filters);
            if ($result) {
                $returnArray[] = $sr;
            }
        }

        return $returnArray;
    }

    protected function evaluateFilter(RowInterface $row, array $filterList): bool
    {
        $result = true;
        $position = 0;
        $subList = [];
        foreach ($filterList as $filter) {
            $operator = $filter[0];
            $field = $filter[1];
            $relation = $filter[2];
            $value = $filter[3];


            if ($operator == ")") {
                $result = $this->evaluateFilter($row, $subList);
                $subList = [];
                continue;
            } elseif ($operator == "(") {
                $filter[0] = " and ";
                $subList[] = $filter;
                continue;
            } elseif (count($subList) > 0) {
                $subList[] = $filter;
                continue;
            }

            switch ($relation) {
                case Relation::EQUAL:
                    $localEval = $row->get($field) == $value;
                    break;

                case Relation::GREATER_THAN:
                    $localEval = $row->get($field) > $value;
                    break;

                case Relation::LESS_THAN:
                    $localEval = $row->get($field) < $value;
                    break;

                case Relation::GREATER_OR_EQUAL_THAN:
                    $localEval = $row->get($field) >= $value;
                    break;

                case Relation::LESS_OR_EQUAL_THAN:
                    $localEval = $row->get($field) <= $value;
                    break;

                case Relation::NOT_EQUAL:
                    $localEval = $row->get($field) != $value;
                    break;

                case Relation::STARTS_WITH:
                    $localEval = str_starts_with($row->get($field), $value);
                    break;

                case Relation::IN:
                    $localEval = in_array($row->get($field), $value);
                    break;

                case Relation::NOT_IN:
                    $localEval = !in_array($row->get($field), $value);
                    break;

                default: // Relation::CONTAINS:
                    $localEval = str_contains($row->get($field), $value);
                    break;
            }

            if ($position == 0) {
                $result = $localEval;
            } elseif ($operator == " and ") {
                $result = $result && $localEval;
                if (!$result) {
                    break;
                }
            } elseif ($operator == " or ") {
                $result = $result || $localEval;
            } else {
                throw new \InvalidArgumentException("Invalid operator: $operator");
            }

            $position++;
        }

        return $result;
    }

    /**
     * Get the filter
     *
     * @param IteratorFilterFormatter $formatter
     * @param string|null $tableName
     * @param array $params
     * @param string $returnFields
     * @return string
     */
    public function format(IteratorFilterFormatter $formatter, string $tableName = null, array &$params = [], string $returnFields = "*"): string
    {
        return $formatter->format($this->filters, $tableName, $params, $returnFields);
    }

    /**
     * @param string $name Field name
     * @param Relation $relation Relation enum
     * @param mixed $value Field string value
     * @return static
     * @desc Add a single string comparison to filter.
     * @deprecated use and() instead
     */
    public function addRelation(string $name, Relation $relation, mixed $value): static
    {
        return $this->and($name, $relation, $value);
    }

    /**
     * @param string $name Field name
     * @param Relation $relation Relation enum
     * @param mixed $value Field string value
     * @return static
     * @desc Add a single string comparison to filter.
     */
    public function and(string $name, Relation $relation, mixed $value): static
    {
        $this->filters[] = [" and ", $name, $relation, $value];
        return $this;
    }

    /**
     * @param string $name Field name
     * @param Relation $relation Relation enum
     * @param mixed $value Field string value
     * @return static
     * @desc Add a single string comparison to filter. This comparison use the OR operator.
     * @deprecated use or() instead
     */
    public function addRelationOr(string $name, Relation $relation, mixed $value): static
    {
        return $this->or($name, $relation, $value);
    }

    /**
     * @param string $name Field name
     * @param Relation $relation Relation enum
     * @param mixed $value Field string value
     * @return static
     * @desc Add a single string comparison to filter. This comparison use the OR operator.
     */
    public function or(string $name, Relation $relation, mixed $value): static
    {
        $this->filters[] = [" or ", $name, $relation, $value];
        return $this;
    }

    /**
     * Add a "("
     * @return static
     */
    public function startGroup(string $name, Relation $relation, mixed $value): static
    {
        $this->filters[] = ["(", $name, $relation, $value];
        return $this;
    }

    /**
     * Add a ")"
     * @return static
     */
    public function endGroup(): static
    {
        $this->filters[] = [")", "", "", ""];
        return $this;
    }

    /**
     * @return array
     */
    public function getRawFilters(): array
    {
        return $this->filters;
    }
}
