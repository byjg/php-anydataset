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
               case Relation::EQUAL:
                    $return = $field . "=" . $value;
                    break;

               case Relation::GREATER_THAN:
                    $return = $field . ">" . $value;
                    break;

               case Relation::LESS_THAN:
                    $return = $field . "<" . $value;
                    break;

               case Relation::GREATER_OR_EQUAL_THAN:
                    $return = $field . ">=" . $value;
                    break;

               case Relation::LESS_OR_EQUAL_THAN:
                    $return = $field . "<=" . $value;
                    break;

               case Relation::NOT_EQUAL:
                    $return = $field . "!=" . $value;
                    break;

               case Relation::STARTS_WITH:
                    $return = " starts-with($field, $value) ";
                    break;

                case Relation::IN:
                    throw new InvalidArgumentException('XPath does not support IN');

                case Relation::NOT_IN:
                    throw new InvalidArgumentException('XPath does not support NOT IN');

               default: // Relation::CONTAINS:
                    $return = " contains($field, $value) ";
                    break;
          }

          return $return;
     }
}
