<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Enum\Relation;

class IteratorFilterXPathFormatter extends IteratorFilterFormatter
{
     /**
      * @inheritDoc
      */
     public function format($filters, $tableName = null, &$params = [], $returnFields = "*")
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
     public function getRelation($name, $relation, $value, &$param)
     {
          $str = is_numeric($value) ? "" : "'";
          $field = "field[@name='" . $name . "'] ";
          $value = " $str$value$str ";

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

               default: // Relation::CONTAINS:
                    $return = " contains($field, $value) ";
                    break;
          }

          return $return;
     }
}
