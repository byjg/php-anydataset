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
     * @param Row $singleRow
     * @return bool
     */
    private function evalString(Row $singleRow): bool
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
                $result[$pos] = match ($relation) {
                    Relation::EQUAL => $result[$pos] && ($valueparam == $value),
                    Relation::GREATER_THAN => $result[$pos] && ($valueparam > $value),
                    Relation::LESS_THAN => $result[$pos] && ($valueparam < $value),
                    Relation::GREATER_OR_EQUAL_THAN => $result[$pos] && ($valueparam >= $value),
                    Relation::LESS_OR_EQUAL_THAN => $result[$pos] && ($valueparam <= $value),
                    Relation::NOT_EQUAL => $result[$pos] && ($valueparam != $value),
                    Relation::STARTS_WITH => $result[$pos] && (str_starts_with(is_null($valueparam) ? "" : $valueparam, $value)),
                    Relation::IN => $result[$pos] && in_array($valueparam, $value),
                    Relation::NOT_IN => $result[$pos] && !in_array($valueparam, $value),
                    default => $result[$pos] && (str_contains(is_null($valueparam) ? "" : $valueparam, $value)),
                };
            }
        }

        $finalResult = $finalResult || $result[$pos];

        return $finalResult;
    }

    /**
     * @param string $name Field name
     * @param Relation $relation Relation enum
     * @param mixed $value Field string value
     * @return static
     * @desc Add a single string comparison to filter.
     */
    public function addRelation(string $name, Relation $relation, mixed $value): static
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
     */
    public function addRelationOr(string $name, Relation $relation, mixed $value): static
    {
        $this->filters[] = [" or ", $name, $relation, $value];
        return $this;
    }

    /**
     * Add a "("
     * @return static
     */
    public function startGroup(): static
    {
        $this->filters[] = ["(", "", "", ""];
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
