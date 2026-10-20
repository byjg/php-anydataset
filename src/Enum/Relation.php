<?php

namespace ByJG\AnyDataset\Core\Enum;

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 */
enum Relation: string
{
    case Equal = '=';
    case LessThan = '<';
    case GreaterThan = '>';
    case LessThanOrEqualTo = '<=';
    case GreaterThanOrEqualTo = '>=';
    case NotEqual = '<>';
    case StartsWith = 'STARTS WITH';
    case Contains = 'CONTAINS';
    case In = 'IN';
    case NotIn = 'NOT IN';
    case IsNull = 'IS NULL';
    case IsNotNull = 'IS NOT NULL';
}