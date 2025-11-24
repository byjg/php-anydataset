<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;
use InvalidArgumentException;

class IteratorFilter
{
    /**
     * Filter array structure constants
     */
    private const OPERATOR = 0;
    private const FIELD = 1;
    private const RELATION = 2;
    private const VALUE = 3;
    
    /**
     * Logical operators
     */
    private const AND_OPERATOR = " and ";
    private const OR_OPERATOR = " or ";
    private const OPEN_GROUP = "(";
    private const CLOSE_GROUP = ")";

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
     * @return RowInterface[]
     */
    public function match(array $array): array
    {
        if (count($this->filters) === 0) {
            return $array;
        }

        $returnArray = [];
        foreach ($array as $row) {
            if ($this->evaluateRow($row)) {
                $returnArray[] = $row;
            }
        }

        return $returnArray;
    }

    /**
     * Get the filter
     *
     * @param IteratorFilterFormatter $formatter
     * @param array $params
     * @return string
     */
    public function format(IteratorFilterFormatter $formatter, array &$params = []): string
    {
        return $formatter->format($this->filters, $params);
    }


    /**
     * @param RowInterface $row
     * @return bool
     */
    private function evaluateRow(RowInterface $row): bool
    {
        return $this->evaluateFilterRecursive($row, $this->filters);
    }

    /**
     * Recursively evaluates a list of filters against a given row
     *
     * @param RowInterface $row The row to evaluate
     * @param array $filterList List of filters in the format [operator, field, relation, value]
     * @param string|null $previousOperator The previous logical operator (and/or)
     * @return bool Whether the row matches the filter criteria
     */
    private function evaluateFilterRecursive(RowInterface $row, array $filterList, ?string $previousOperator = null): bool
    {
        if (empty($filterList)) {
            return true;
        }

        $result = true;
        $position = 0;
        $subList = [];

        foreach ($filterList as $filter) {
            $operator = $filter[self::OPERATOR];

            if ($operator == self::CLOSE_GROUP) {
                $result = $this->handleCloseGroup($row, $subList, $previousOperator);
                $subList = [];
                continue;
            }

            if ($operator == self::OPEN_GROUP) {
                $shouldReturn = $this->handleOpenGroup($filter, $result, $previousOperator, $subList);
                if ($shouldReturn !== null) {
                    return $shouldReturn;
                }
                continue;
            }

            if (!empty($subList)) {
                $subList[] = $filter;
                continue;
            }

            $localEval = $this->evaluateFilter($row, $filter);
            $result = $this->applyOperatorToResult($operator, $result, $localEval, $position);

            if ($position > 0 && $operator == self::AND_OPERATOR && !$result) {
                break;
            }

            $previousOperator = $operator;
            $position++;
        }

        return $result;
    }

    /**
     * Handle closing group by evaluating the sublist
     *
     * @param RowInterface $row
     * @param array $subList
     * @param string|null $previousOperator
     * @return bool
     */
    private function handleCloseGroup(RowInterface $row, array $subList, ?string $previousOperator): bool
    {
        return $this->evaluateFilterRecursive($row, $subList, $previousOperator);
    }

    /**
     * Handle opening group and check for short-circuit
     *
     * @param array $filter
     * @param bool $result
     * @param string|null &$previousOperator
     * @param array &$subList
     * @return bool|null Returns false if short-circuit, null otherwise
     */
    private function handleOpenGroup(array $filter, bool $result, ?string &$previousOperator, array &$subList): ?bool
    {
        $filter[self::OPERATOR] = $previousOperator ?? self::AND_OPERATOR;
        $previousOperator = $filter[self::OPERATOR];

        if ($previousOperator == self::AND_OPERATOR && $result === false) {
            return false;
        }

        $subList[] = $filter;
        return null;
    }

    /**
     * Evaluate a single filter against a row
     *
     * @param RowInterface $row
     * @param array $filter
     * @return bool
     */
    private function evaluateFilter(RowInterface $row, array $filter): bool
    {
        $field = $filter[self::FIELD];
        $relation = $filter[self::RELATION];
        $value = $filter[self::VALUE];
        $fieldValue = $this->getFieldValue($row, $field);

        return $this->evaluateCondition($row, $field, $relation, $value, $fieldValue);
    }

    /**
     * Apply the operator to combine the result with the local evaluation
     *
     * @param string $operator
     * @param bool $result
     * @param bool $localEval
     * @param int $position
     * @return bool
     */
    private function applyOperatorToResult(string $operator, bool $result, bool $localEval, int $position): bool
    {
        if ($position == 0) {
            return $localEval;
        }

        if ($operator == self::AND_OPERATOR) {
            return $result && $localEval;
        }

        if ($operator == self::OR_OPERATOR) {
            return $result || $localEval;
        }

        throw new InvalidArgumentException("Invalid operator: $operator");
    }

    /**
     * Evaluates a single condition based on the relation type
     *
     * @param RowInterface $row The row being evaluated
     * @param string $field The field name
     * @param Relation $relation The relation type
     * @param mixed $value The value to compare against
     * @param mixed $fieldValue The field value (pre-processed)
     * @return bool Whether the condition is true
     */
    private function evaluateCondition(RowInterface $row, string $field, Relation $relation, mixed $value, mixed $fieldValue): bool
    {
        return match ($relation) {
            Relation::EQUAL => $fieldValue == $value,
            Relation::GREATER_THAN => $fieldValue > $value,
            Relation::LESS_THAN => $fieldValue < $value,
            Relation::GREATER_OR_EQUAL_THAN => $fieldValue >= $value,
            Relation::LESS_OR_EQUAL_THAN => $fieldValue <= $value,
            Relation::NOT_EQUAL => $fieldValue != $value,
            Relation::STARTS_WITH => str_starts_with($fieldValue, $value),
            Relation::IN => in_array($fieldValue, $value),
            Relation::NOT_IN => !in_array($fieldValue, $value),
            Relation::IS_NULL => is_null($row->get($field)),
            Relation::IS_NOT_NULL => !is_null($row->get($field)),
            default => str_contains($fieldValue, $value),
        };
    }

    /**
     * Get field value and handle nulls for string operations
     *
     * @param RowInterface $row
     * @param string $field
     * @return mixed
     */
    private function getFieldValue(RowInterface $row, string $field): mixed
    {
        $value = $row->get($field);

        // For string operations, we convert null to empty string
        if (is_null($value)) {
            return "";
        }

        return $value;
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
    public function and(string $name, Relation $relation, mixed $value = null): static
    {
        $this->filters[] = [self::AND_OPERATOR, $name, $relation, $value];
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
        $this->filters[] = [self::OR_OPERATOR, $name, $relation, $value];
        return $this;
    }

    /**
     * Add a "("
     * @return static
     */
    public function startGroup(string $name, Relation $relation, mixed $value): static
    {
        $this->filters[] = [self::OPEN_GROUP, $name, $relation, $value];
        return $this;
    }

    /**
     * Add a ")"
     * @return static
     */
    public function endGroup(): static
    {
        $this->filters[] = [self::CLOSE_GROUP, "", "", ""];
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
