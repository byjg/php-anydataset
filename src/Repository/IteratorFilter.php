<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Database\SQLHelper;
use ByJG\AnyDataset\Enum\Relation;

class IteratorFilter
{

    const XPATH = 1;
    const SQL = 2;

    /**
     * @var array
     */
    private $_filters;

    /**
     * @desc IteratorFilter Constructor
     */
    public function __construct()
    {
        $this->_filters = array();
    }

    /**
     * @param
     * @return string - XPath String
     * @desc Get the XPATH string
     */
    public function getXPath()
    {
        $param = "";
        $xpathFilter = $this->getFilter(IteratorFilter::XPATH, $param);

        if ($xpathFilter == "") {
            return "/anydataset/row";
        } else {
            return "/anydataset/row[" . $xpathFilter . "]";
        }
    }

    /**
     * Get the SQL string
     *
     * @param string $tableName
     * @param array &$params
     * @param string $returnFields
     * @return string
     */
    public function getSql($tableName, &$params, $returnFields = "*")
    {
        $params = array();

        $sql = "select @@returnFields from @@tableName ";
        $sqlFilter = $this->getFilter(IteratorFilter::SQL, $params);
        if ($sqlFilter != "") {
            $sql .= " where @@sqlFilter ";
        }

        return str_replace(
                [
                    "@@returnFields",
                    "@@tableName",
                    "@@sqlFilter"
                ],
                [
                    $returnFields,
                    $tableName,
                    $sqlFilter
                ],
                $sql
        );
    }

    /**
     *
     * @param $array
     * @return SingleRow[]
     */
    public function match($array)
    {
        $returnArray = array();

        foreach ($array as $sr) {
            if ($this->evalString($sr)) {
                $returnArray[] = $sr;
            }
        }

        return $returnArray;
    }

    /**
     * Return a filter in SQL or XPATH
     *
     * @param string $type use XPATH or SQL
     * @param array $param
     * @return string
     */
    public function getFilter($type, &$param)
    {
        $filter = "";
        $param = array();

        $previousValue = null;
        foreach ($this->_filters as $value) {
            if ($value[0] == "(") {
                if (!is_null($previousValue)) {
                    $filter .= " or ( ";
                } else {
                    $filter .= " ( ";
                }
            } elseif ($value[0] == ")") {
                $filter .= ")";
            } else {
                if ((!is_null($previousValue)) && ($previousValue[0] != "(")) {
                    $filter .= $value[0];
                }
                if ($type == self::XPATH) {
                    $filter .= $this->getStrXpathRelation($value[1], $value[2], $value[3]);
                } elseif ($type == self::SQL) {
                    $filter .= $this->getStrSqlRelation($value[1], $value[2], $value[3], $param);
                }
            }
            $previousValue = $value;
        }

        return $filter;
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return string Xpath String
     * @desc Private method to get a Xpath string to a single string comparison
     */
    private function getStrXpathRelation($name, $relation, $value)
    {
        $str = is_numeric($value) ? "" : "'";
        $field = "field[@name='" . $name . "'] ";
        $value = " $str$value$str ";

        $result = "";
        switch ($relation) {
            case Relation::EQUAL: {
                    $result = $field . "=" . $value;
                    break;
                }
            case Relation::GREATER_THAN: {
                    $result = $field . ">" . $value;
                    break;
                }
            case Relation::LESS_THAN: {
                    $result = $field . "<" . $value;
                    break;
                }
            case Relation::GREATER_OR_EQUAL_THAN: {
                    $result = $field . ">=" . $value;
                    break;
                }
            case Relation::LESS_OR_EQUAL_THAN: {
                    $result = $field . "<=" . $value;
                    break;
                }
            case Relation::NOT_EQUAL: {
                    $result = $field . "!=" . $value;
                    break;
                }
            case Relation::STARTS_WITH: {
                    $result = " starts-with($field, $value) ";
                    break;
                }
            case Relation::CONTAINS: {
                    $result = " contains($field, $value) ";
                    break;
                }
        }
        return $result;
    }

    /**
     *
     * @param string $name
     * @param int $relation
     * @param string $value
     * @param string[] $param
     * @return string
     */
    private function getStrSqlRelation($name, $relation, $value, &$param)
    {
        $value = trim($value);
        $paramName = $name;
        $i = 0;
        while (array_key_exists($paramName, $param)) {
            $paramName = $name . ($i++);
        }

        $param[$paramName] = $value;

        $result = "";
        $field = " $name ";
        $valueparam = " [[" . $paramName . "]] ";
        switch ($relation) {
            case Relation::EQUAL: {
                    $result = $field . "=" . $valueparam;
                    break;
                }
            case Relation::GREATER_THAN: {
                    $result = $field . ">" . $valueparam;
                    break;
                }
            case Relation::LESS_THAN: {
                    $result = $field . "<" . $valueparam;
                    break;
                }
            case Relation::GREATER_OR_EQUAL_THAN: {
                    $result = $field . ">=" . $valueparam;
                    break;
                }
            case Relation::LESS_OR_EQUAL_THAN: {
                    $result = $field . "<=" . $valueparam;
                    break;
                }
            case Relation::NOT_EQUAL: {
                    $result = $field . "!=" . $valueparam;
                    break;
                }
            case Relation::STARTS_WITH: {
                    $param[$paramName] = $value . "%";
                    $result = $field . " like " . $valueparam;
                    break;
                }
            case Relation::CONTAINS: {
                    $param[$paramName] = "%" . $value . "%";
                    $result = $field . " like " . $valueparam;
                    break;
                }
        }

        return $result;
    }

    /**
     *
     * @param SingleRow $singleRow
     * @return string
     */
    private function evalString(SingleRow $singleRow)
    {
        $result = array();
        $finalResult = false;
        $pos = 0;

        $result[0] = true;

        foreach ($this->_filters as $filter) {
            if (($filter[0] == ")") || ($filter[0] == " or ")) {
                $finalResult |= $result[$pos];
                $result[++$pos] = true;
            }

            $name = $filter[1];
            $relation = $filter[2];
            $value = $filter[3];

            $field = $singleRow->getField($name);

            if (!is_array($field)) $field = array($field);

            foreach ($field as $valueparam) {
                switch ($relation) {
                    case Relation::EQUAL: {
                            $result[$pos] &= ($valueparam == $value);
                            break;
                        }
                    case Relation::GREATER_THAN: {
                            $result[$pos] &= ($valueparam > $value);
                            break;
                        }
                    case Relation::LESS_THAN: {
                            $result[$pos] &= ($valueparam < $value);
                            break;
                        }
                    case Relation::GREATER_OR_EQUAL_THAN: {
                            $result[$pos] &= ($valueparam >= $value);
                            break;
                        }
                    case Relation::LESS_OR_EQUAL_THAN: {
                            $result[$pos] &= ($valueparam <= $value);
                            break;
                        }
                    case Relation::NOT_EQUAL: {
                            $result[$pos] &= ($valueparam != $value);
                            break;
                        }
                    case Relation::STARTS_WITH: {
                            $result[$pos] &= (strpos($valueparam, $value) === 0);
                            break;
                        }
                    case Relation::CONTAINS: {
                            $result[$pos] &= (strpos($valueparam, $value) !== false);
                            break;
                        }
                }
            }
        }

        $finalResult |= $result[$pos];

        return $finalResult;
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return void
     * @desc Add a single string comparison to filter.
     */
    public function addRelation($name, $relation, $value)
    {
        $this->_filters[] = array(" and ", $name, $relation, $value);
    }

    /**
     * @param string $name Field name
     * @param int $relation Relation enum
     * @param string $value Field string value
     * @return void
     * @desc Add a single string comparison to filter. This comparison use the OR operator.
     */
    public function addRelationOr($name, $relation, $value)
    {
        $this->_filters[] = array(" or ", $name, $relation, $value);
    }

    /**
     * Add a "("
     *
     */
    public function startGroup()
    {
        $this->_filters[] = array("(", "", "", "");
    }

    /**
     * Add a ")"
     *
     */
    public function endGroup()
    {
        $this->_filters[] = array(")", "", "", "");
    }
}
