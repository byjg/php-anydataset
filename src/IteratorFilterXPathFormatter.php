<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;
use InvalidArgumentException;
use Override;

class IteratorFilterXPathFormatter extends IteratorFilterFormatter
{
     /**
      * @inheritDoc
      */
    #[Override]
    public function format(array $filters, array &$params = []): string
    {
          $param = [];
          $xpathFilter = $this->getFilter($filters, $param);

          if ($xpathFilter == "") {
               return "/anydataset/row";
          }

          return "/anydataset/row[" . $xpathFilter . "]";
    }

     /**
      * @inheritDoc
      */
    #[Override]
    public function getRelation(string $name, Relation $relation, mixed $value): string
    {
          if (is_array($value)) {
               throw new InvalidArgumentException('XPath does not support array values');
          }

          $str = is_numeric($value) ? "" : "'";
          $field = "field[@name='" . $name . "'] ";
          if (is_string($value)) {
              $value = " $str$value$str ";
          }

          switch ($relation) {
               case Relation::Equal:
                    $return = $field . "=" . $value;
                    break;

               case Relation::GreaterThan:
                    $return = $field . ">" . $value;
                    break;

               case Relation::LessThan:
                    $return = $field . "<" . $value;
                    break;

               case Relation::GreaterThanOrEqualTo:
                    $return = $field . ">=" . $value;
                    break;

               case Relation::LessThanOrEqualTo:
                    $return = $field . "<=" . $value;
                    break;

               case Relation::NotEqual:
                    $return = $field . "!=" . $value;
                    break;

               case Relation::StartsWith:
                    $return = " starts-with($field, $value) ";
                    break;

                case Relation::In:
                    throw new InvalidArgumentException('XPath does not support IN');

                case Relation::NotIn:
                    throw new InvalidArgumentException('XPath does not support NOT IN');

               default: // Relation::Contains:
                    $return = " contains($field, $value) ";
                    break;
          }

          return $return;
     }
}
