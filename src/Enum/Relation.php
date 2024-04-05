<?php

namespace ByJG\AnyDataset\Core\Enum;

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 */
class Relation
{

    /**
     * "Equal" relational operator
     */
    const EQUAL = 0;

    /**
     * "Less than" relational operator
     */
    const LESS_THAN = 1;

    /**
     * "Greater than" relational operator
     */
    const GREATER_THAN = 2;

    /**
     * "Less or Equal Than" relational operator
     */
    const LESS_OR_EQUAL_THAN = 3;

    /**
     * "Greater or equal than" relational operator
     */
    const GREATER_OR_EQUAL_THAN = 4;

    /**
     * "Not equal" relational operator
     */
    const NOT_EQUAL = 5;

    /**
     * "Starts with" unary comparator
     */
    const STARTS_WITH = 6;

    /**
     * "Contains" unary comparator
     */
    const CONTAINS = 7;

    /**
     * "In" unary comparator
     */
    const IN = 8;

    /**
     * "Not In" unary comparator
     */
    const NOT_IN = 9;
}
