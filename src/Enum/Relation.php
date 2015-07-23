<?php

namespace ByJG\AnyDataset\Enum;

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 * @package xmlnuke
 */
class Relation
{
	/**
	 * "Equal" relational operator
	 */
	const Equal = 0;

	/**
	 * "Less than" relational operator
	 */
	const LessThan = 1;

	/**
	 * "Greater than" relational operator
	 */
	const GreaterThan = 2;

	/**
	 * "Less or Equal Than" relational operator
	 */
	const LessOrEqualThan = 3;
	/**
	 * "Greater or equal than" relational operator
	 */
	const GreaterOrEqualThan = 4;
	/**
	 * "Not equal" relational operator
	 */
	const NotEqual = 5;
	/**
	 * "Starts with" unary comparator
	 */
	const StartsWith = 6;
	/**
	 * "Contains" unary comparator
	 */
	const Contains = 7;
}
