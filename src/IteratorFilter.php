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
        $returnArray = [];
        $filterList = $this->format(new IteratorFilterFormatter());

        if (empty($filterList)) {
            return $array;
        }

        foreach ($array as $sr) {
            $rowArr = $sr->toArray();
            $rowEval = [];
            foreach ($rowArr as $key => $value) {
                $rowEval["%$key"] = is_numeric($value) ? $value : "'$value'";
            }

            $result = eval("return " . strtr($filterList, $rowEval) . ";");
            if ($result) {
                $returnArray[] = $sr;
            }
        }

        return $returnArray;
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
