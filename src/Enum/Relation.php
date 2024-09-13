<?php

namespace ByJG\AnyDataset\Core\Enum;

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 */
enum Relation
{
    case EQUAL;
    case LESS_THAN;
    case GREATER_THAN;
    case LESS_OR_EQUAL_THAN;
    case GREATER_OR_EQUAL_THAN;
    case NOT_EQUAL;
    case STARTS_WITH;
    case CONTAINS;
    case IN;
    case NOT_IN;
}